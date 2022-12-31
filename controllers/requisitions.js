theApp.controller("requisitionsCtlr", function($scope, $http, userDetails){
	
	/*Generating requisitions list */
	$http.get("../crud/read/getRequisitions.php", {params: {station: userDetails.getStation()}}).then(function(response){
		$scope.requisitions = response.data;
		//console.log(response.data);
	}, function(response){
		console.log(response.data);
	});
		
});

theApp.controller("create_requisitionCtlr", function($scope, $timeout, $http, userDetails, lineDetails, httpResponse){
	
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
	$scope.reqItemRate = function(item, index){ lineDetails.applyRate(item, index, req_details, $scope.rows);}

	/* Computing the Item total from Qty */
	$scope.reqComputeSubTotal = function(qty,item, idx){ lineDetails.computeSubTotal(qty,item, idx, req_details);}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getReqItemsList.php").then(res => $scope.items =res);
	/*$http.get("../crud/read/getReqItemsList.php").then(function(response){
		$scope.items = response.data;
	});*/
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [{ID:1}];
	
	/* Function to add a row to the orders details */
	
	$scope.addRow = () => lineDetails.addRow_create($scope.rows, req_details);/*function(){
		let counter = $scope.rows.length + 1;
		$scope.rows.push({ID: counter});
	}*/
	
	/* Function to reomve a row from the orders details */
	
	$scope.removeRow = (index) => lineDetails.removeRow_create($scope.rows, index, "Requisition must have atleast one item", req_details);/*function(index){
		if($scope.rows.length === 1){
			alert("Order must have atleast one item");
		}else{
			$scope.rows.splice(index, 1);
			req_details.splice(index, 1);//as well remove the details object from this array
		}
	}*/
	
	
	/* Validate Form Data and submit */
	$scope.validate = function (){
		
		let form_values = {category:$scope.category, station:$scope.station, details:req_details, userID: userDetails.getUserID()};
		console.log(form_values);
		
		$http.post("../crud/create/add_requisition.php", form_values).then(function(response){
			httpResponse.success(1, response.data.message);
			//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
		}, function(response){
			httpResponse.error(0, response.data);	
		});
	}

});

theApp.controller("edit_requisitionCtlr", function($scope, $http, $routeParams, $q, httpResponse, lineDetails){
	let editreq_details = [];
	let deleted_req_lines = [];
	let promisesArr = [];
	
	$http.post("../crud/read/getRequisition.php", {reqNo: $routeParams.reqNo}).then(function(response){
		$scope.station = response.data.requisition[0].Station;
		$scope.category = response.data.requisition[0].Category;
		angular.forEach(response.data.req_details, function(v)  {v.deleted = false})//Add deleted property for toggling deleted class in ngRepeat
		$scope.requisition_items = response.data.req_details;
		$scope.getGrandTotal = gTotal(response.data.req_details);
		console.log(response.data);
	}, function(response){
		console.log(response.data);
	});
	
	/* When user chooses item, apply the rate */
	$scope.reqItemRate = function(item, index){ lineDetails.applyRate(item, index, editreq_details,	$scope.rows);}

	/* Computing the Item total from Qty */
	$scope.reqComputeSubTotal = function(qty,item, idx){ lineDetails.computeSubTotal(qty,item, idx, editreq_details);}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getReqItemsList.php").then(res => $scope.items =res);
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [/*{ID:1}*/];
	
	/* Function to add a row to the orders details */
	$scope.addRow = /*() => lineDetails.addRow_edit($scope.rows, $scope.showEditDetails, $scope.requisition_items);*/function(){
		let counter = ($scope.rows.length + 1) + $scope.requisition_items.length;
		if(!$scope.showEditDetails){
			$scope.showEditDetails = true;
			$scope.rows.push({ID: counter});
		}else{
			$scope.rows.push({ID: counter});
		}
	}
	
	/* Function to reomve a temporary row from the orders details */
	$scope.removeRow = function(index){
		$scope.rows.splice(index, 1);
		editreq_details.splice(index,1);
		if($scope.rows.length === 0){
			$scope.showEditDetails = false;
		}
	}
	
	/* Function to delete a existing record from the orders details */
	$scope.deleteLineItem = function(detailsID, index){
		if($scope.requisition_items[index].deleted === false){
			$scope.requisition_items[index].deleted = true;
			deleted_req_lines.push({detailsID, index});
		}else{
			$scope.requisition_items[index].deleted = false;
			deleted_req_lines.splice(index,1);
		}
		//console.log(deleted_order_lines, $scope.order_items[index]);
	}
	
	//Delete Order Line Promise
	function deletePromise(_data){
		return $http.get("../crud/delete/deleteReqLine.php", {params: _data});
	}
	
	//Add Order Line Promise
	function addPromise(_data){
		return $http.post("../crud/create/addReqLine.php", _data);
	}
	
	function collectPromises(){
		let collected = false;
		
		if(deleted_req_lines.length !== 0){
			promisesArr.push(deletePromise({deletedLines: deleted_req_lines}));
			collected = true;
		}
		
		if(editreq_details.length !== 0){
			promisesArr.push(addPromise({reqNo: $routeParams.reqNo, addedLines: editreq_details}));
			collected = true;
		}
		
		return collected;
	}
	
	$scope.validate = function (){ 
		if(collectPromises()){
			
			$q.all(promisesArr).then(function(response){
				if(collectPromises.length === 2){
					if(response[0].data.status === 1 && response[1].data.status === 1){
						httpResponse.success(1, "Updated Succesfuly");
					}else if(response[0].data.status === 0 || response[1].data.status === 0){
						httpResponse.success(0, "One or both operations failed, check and try again");
					}
				}else{
					if(response[0].data.status === 1){
						httpResponse.success(1, "Updated Succesfuly");
					}else{
						httpResponse.success(0, "The operation failed, Please try again");
					}
				}
				//console.log(response[0].data);
			},function(response){
				httpResponse.error(0, response.data);
				//console.log(response);
			});
			
		}else{
			httpResponse.success(2, "Nothing was Edited");
			//console.log("Nothing colected");
		}
		//console.log("Hado #33: Sokatsui", editorder_details, deleted_order_lines);
	}
	
});

