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
	
	/*Generate record for editing */
	if($routeParams.pdtID !== undefined){
		$scope.pagetitle = "Edit Product";
		$scope.pgtitle = "Edit";
		let pdtID = $routeParams.pdtID;
		$http.post("../crud/read/getProduct.php", {pdtID: pdtID}).then(function(response){
			if(response.data.status === 1){
				let res = response.data.message;
				$scope.desc = res.Description;
				$scope.sale_name = res.SaleName;
				$scope.sale_price = res.UnitSalePrice;
				$scope.cost_price = res.UnitCostPrice;
				$scope.cat = res.Category;
				$scope.min_stock = Number(res.StockLevel);
				$scope.mesr_sold = Number(res.MeasureSold);
				$scope.untqty = res.UnitQty;
				$scope.status = res.Status;
				$scope.preptime = Number(res.PrepTime);
			}
			//console.log(response.data);
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
		let form_values = Object.fromEntries(formInputs);
		
		//console.log(form_values);
		
		if($routeParams.pdtID === undefined){
			$http.post("../crud/create/add_product.php", form_values).then(function(response){
				httpResponse.success(1, response.data.message);
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
					editedfields[k] = v.$modelValue;
				}
			});
			
			$http.post("../crud/update/setProduct.php", editedfields).then(function(response){
				httpResponse.success(1, response.data.message);
				//console.log(response.data);
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			});
			
		}
		
	}

});