theApp.controller("requisitionsCtlr", function($scope, $http, userDetails){
	
	/*Generating requisitions list */
	$http.get("../crud/read/getRequisitions.php", {params: {station: userDetails.getStation()}}).then(function(response){
		$scope.requisitions = response.data;
		//console.log(response.data);
	}, function(response){
		console.log(response.data);
	});
	
	/*$scope.requisitions = [
		{reqNo: "REQ001",date: "12/11/2022",category: "Eats",station: "Restaurant",status: "Submitted",staff: "John",amount: "120,000"},
		{reqNo: "REQ002",date: "13/11/2022",category: "Drinks",station: "Hall",status: "Pending",staff: "Mary",amount: 0}
	
	];*/
	
});

theApp.controller("create_requisitionCtlr", function($scope, $timeout, $http, userDetails){
	
	$scope.station = userDetails.getStation();

	let req_details = [];//to contain requisition details for form submission
	
	/* Generate Requisition details Grand Total */
	$scope.getGrandTotal = /*gTotal(req_details);*/function() {
		let grandtotal = 0;
		if(req_details.length !== 0){
			for(let y = 0; y < req_details.length; y++){
				grandtotal += req_details[y].subtotal ;
			}
		}
		return grandtotal;
	}
	
	/* When user chooses item, apply the rate */
	$scope.reqItemRate = function(x, idx){
		
		let qty_input = document.getElementsByClassName("qty_input");
		let rate_input = document.getElementsByClassName("rate_input");
		for(let i = 0; i<rate_input.length; i++){
			if(idx === i){
				rate_input[i].value = x.rate;//rate is from the object in the ng-options value when the item is selected
				
				req_details[idx] = {pdtNo:x.value/*, item:x.label*/};//adding the requisition details' objects to array by the current index
				
				//Apply the total if the qty field is already filled
				if(qty_input[i].value !== undefined){
					let qty = qty_input[i].value;
					$scope.reqComputeSubTotal(qty,x,idx);
				}
			}
		}
		
	}

	/* Computing the Item total from Qty */
	$scope.reqComputeSubTotal = function(qty,item, idx){
		let subtotal_input = document.getElementsByClassName("total_input");
		if(item !== undefined && qty !== undefined ){
			for(let x = 0; x < subtotal_input.length; x++){
				if(idx === x){
					subtotal_input[x].value = qty * item.rate;
					
					//adding the requisition details' objects' properties by the current index
					req_details[idx].qty = qty;
					req_details[idx].rate = item.rate;
					req_details[idx].subtotal = qty * item.rate;
					//console.log(qty + " " + item);
				}
			}
		}
	}
	
	/* Generating the options of the Item select tag */
	$http.get("../crud/read/getReqItemsList.php").then(function(response){
		$scope.items = response.data;
	});
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [{ID:1}];
	
	/* Function to add a row to the orders details */
	
	$scope.addRow = function(){
		let counter = $scope.rows.length + 1;
		$scope.rows.push({ID: counter});
		/*$scope.rows.push($scope.rows.length + 1);*/
	}
	
	/* Function to reomve a row from the orders details */
	
	$scope.removeRow = function(index){
		//alert(index);
		if($scope.rows.length === 1){
			alert("Order must have atleast one item");
		}else{
			$scope.rows.splice(index, 1);
			req_details.splice(index, 1);//as well remove the details object from this array
		}
	}
	
	
	/* Validate Form Data and submit */
	$scope.validate = function (){
		
		let form_values = {category:$scope.category, station:$scope.station, details:req_details, userID: userDetails.getUserID()};
		console.log(form_values);
		//for(let prop in form_values){console.log(form_values[prop])};
		//for(let z=0; z<req_details.length; z++){console.log(req_details[z])}
		$http.post("../crud/create/add_requisition.php", form_values).then(function(response){
			//alert(response.data);
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
			//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			
		}, function(response){
			//alert(response.statusText);
			toggleLoader("block");
			
			$timeout(function(){
				displayResponseBox(false);
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
			}, 2000);
			
		});
	}

});

theApp.controller("edit_requisitionCtlr", function($scope, $http, $routeParams){

	$http.post("../crud/read/getRequisition.php", {reqNo: $routeParams.reqNo}).then(function(response){
		$scope.station = response.data.requisition[0].Station;
		$scope.category = response.data.requisition[0].Category;
		$scope.requisition_items = response.data.req_details;
		$scope.getGrandTotal = gTotal(response.data.req_details);
		console.log(response.data);
	}, function(response){
		console.log(response.data);
	});
	/*let edit_requisitionPage = [{"station": "Reception",
								"category": "Drinks",
								"requisition_items": [
				{"number": 1,"item": "Mountain Dew", "qty": 24,"rate": 770, "total": "19,500"},
				{"number": 2,"item": "Coke", "qty": 24,"rate": 770, "total": "19,500"},
				{"number": 3,"item": "Smirnoff Black", "qty": 25,"rate": 2250, "total": "69,000"}
			]
		}
	]
	
	$scope.station = edit_requisitionPage[0].station;
	$scope.category = edit_requisitionPage[0].category;
	$scope.requisition_items = edit_requisitionPage[0].requisition_items;*/
});

