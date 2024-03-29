theApp.controller("productsCtlr", function($scope, $http){
	$scope.pagetitle = "Products List";
	$http.get("../crud/read/getProducts.php").then(function(response){
		$scope.products = response.data;
	},function(response){
		
	});
	
});

theApp.controller("edit_productCtlr", function($scope, $timeout, $http, $routeParams, httpResponse){
	/* Pre-select the Status field */
	$scope.status = "Active";
	
	$scope.changeCheckSold = function(){
		if($scope.item_sold_check){
			$scope.item_bought_check = false;
			$scope.showMainPdtField = true;
		}else{
			$scope.showMainPdtField = false;
		}
	}
	
	$scope.changeCheckBought = function(){
		if($scope.item_bought_check){
			$scope.item_sold_check = false;
			$scope.showMainPdtField = false;
		}
	}
	
	$http.get("../crud/read/getParentProductList.php").then(function(response){
		console.log(response.data);
		$scope.parentPdtsList = response.data;
	},function(response){
	
	});
	
	/*Generate record for editing */
	if($routeParams.pdtID !== undefined){
		$scope.pagetitle = "Edit Product";
		$scope.pgtitle = "Edit";
		let pdtID = $routeParams.pdtID;
		$http.post("../crud/read/getProduct.php", {pdtID: pdtID}).then(function(response){
			if(response.data.status === 1){
				let res = response.data.message[0];
				$scope.desc = res.Description;
				$scope.sale_name = res.Salename;
				$scope.sale_price = res.UnitSalePrice;
				$scope.cost_price = res.UnitCostPrice;
				$scope.cat = res.Category;
				$scope.min_stock = Number(res.StockLevel);
				$scope.mesr_sold = Number(res.MeasureSold);
				$scope.untqty = res.UnitQty;
				$scope.status = res.Status;
				$scope.preptime = Number(res.PrepTime);
				$scope.stkl_rec = Number(res.Reception);
				$scope.stkl_res = Number(res.Restaurant);
				$scope.stkl_bar = Number(res.Bar);
				
				/*if(res.UnitCostPrice){
					document.getElementById('check_bought').checked = true;
					$scope.item_bought_check = true;
				}else{
					document.getElementById('check').checked = true;
					$scope.item_sold_check = true;
				}*/
			}
			console.log(response.data);
		},function(response){
			console.log(response.data);
		});
	}else{
		$scope.pagetitle = "Add Product";
		$scope.pgtitle = "Add";
	}
	
	/* Validate Form Data and submit */
	$scope.validate = function (){
				
		let form = document.getElementsByName("product_form")[0];
		let formInputs = new FormData(form);
		for(let [name, value] of formInputs){
			if(name === 'description'){
				formInputs.set(name, capitaliseFirstLetter(value));
			}
			
			if(name === 'salename'){
				if(value !== ' '){
					formInputs.set(name, capitaliseFirstLetter(value));
				}
			}
		}
		let form_values = Object.fromEntries(formInputs);
		
		//console.log(form_values);
		
		if($routeParams.pdtID === undefined){console.log(form_values);
			$http.post("../crud/create/add_product.php", form_values).then(function(response){
				httpResponse.success(1, response.data.message);
				exitEditMode("products_btn");
				console.log(response.data);
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			});
		}else{
			let editedfields = {pdtID: $routeParams.pdtID};
			/*Object.keys(form_values).forEach(item => {
				console.log(typeof(item));
			});*/
			
			/*let arr = ['desc', 'cat', 'status'];
			for(let v = 0; v < arr.length; v++){
				console.log(`form.${arr[v]}.$dirty`, $scope.product_form.cat.$dirty, $scope.product_form.arr[v].$dirty);
				
			};*/
			
			//* Use this method as one that works to only choose the edited fields
			angular.forEach($scope.product_form, function(v, k){
				if(typeof v === 'object' && v.hasOwnProperty('$modelValue') && v.$dirty){
					if(k === 'description' || k === 'salename'){
						editedfields[k] = capitaliseFirstLetter(v.$modelValue);
					}else{
						editedfields[k] = v.$modelValue;
					}
					if(k === 'reception' || k === 'restaurant' || k === 'bar'){
						editedfields['stocklevel_edit'] = 1;
					}else{
						editedfields['stocklevel_edit'] = 0;
					}
				}
			});
			console.log(editedfields);
			$http.post("../crud/update/setProduct.php", editedfields).then(function(response){
				httpResponse.success(1, response.data.suc_message);
				console.log(response.data);
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			});
			
		}
		
	}

});
