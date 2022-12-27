theApp.controller("productsCtlr", function($scope, $http){
	$scope.pagetitle = "Products List";
	$http.get("../crud/read/getProducts.php").then(function(response){
		$scope.products = response.data;
	},function(response){
		
	});
	
	/*$scope.products = [
		{ID: "PD101", description: "Chicken", category: "Eats", minstocklevel: 8, status: "Active"},
		{ID: "PD110", description: "Mountain Dew", category: "Drinks", minstocklevel: 10, status: "Active"},
		{ID: "PD145", description: "Nile Gold", category: "Drinks", minstocklevel: 2, status: "Discontinued"}
	]*/
	
});

theApp.controller("edit_productCtlr", function($scope, $timeout, $http, $routeParams){
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
		/*let form_values = {};
		if(angular.isDefined($scope.item_sold_check)){
			form_values.item_sold_chk = $scope.item_sold_check
		}else{
			form_values.item_sold_chk = false;
		}
		if(angular.isDefined($scope.desc)){
			form_values.desc = $scope.desc;
		}
		if(angular.isDefined($scope.sale_name)){
			form_values.sale_name = $scope.sale_name;
		}
		if(angular.isDefined($scope.sale_price)){
			form_values.sale_price = $scope.sale_price;
		}
		if(angular.isDefined($scope.cost_price)){
			form_values.cost_price = $scope.cost_price;
		}
		if(angular.isDefined($scope.cat)){
			form_values.cat = $scope.cat;
		}
		if(angular.isDefined($scope.min_stock)){
			form_values.min_stock = $scope.min_stock;
		}
		if(angular.isDefined($scope.mesr_sold)){
			form_values.mesr_sold = $scope.mesr_sold;
		}else{
			form_values.mesr_sold = 1;
		}
		if(angular.isDefined($scope.untqty)){
			form_values.untqty = $scope.untqty;
		}
		if(angular.isDefined($scope.status)){
			form_values.status = $scope.status;
		}*/
		
		let form = document.getElementsByName("product_form")[0];
		let formInputs = new FormData(form);
		let form_values = Object.fromEntries(formInputs);
		
		console.log(form_values);
		
		if($routeParams.pdtID === undefined){
			/*$http.post("../crud/create/add_product.php", form_values).then(function(response){
				
				toggleLoader("block");
				
				$timeout(function(){
					if(response.data.status === 1){
						displayResponseBox(true, response.data.message);
					}else{
						displayResponseBox(false, response.data.message);
					}
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				
			}, function(response){
				
				toggleLoader("block");
				
				$timeout(function(){
					displayResponseBox(false);
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			});*/
			console.log("Hado #12: Sojiosabaku");
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
				toggleLoader("block");
				
				$timeout(function(){
					if(response.data.status === 1){
						displayResponseBox(1, response.data.message);
					}else if(response.data.status === 2){
						displayResponseBox(2, response.data.message);
					}else{
						displayResponseBox(0, response.data.message);
					}
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				console.log(response.data);
			}, function(response){
				console.log(response.data);
				/*toggleLoader("block");
				
				$timeout(function(){
					displayResponseBox(false);
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);*/
			});
			
		}
		
	}

});