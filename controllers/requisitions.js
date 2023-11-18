theApp.controller("requisitionsCtlr", function($scope, $http, userDetails,lineDetails){
	
	/*$scope.startFilter = function(){
		if($scope.filterApplied == true){
			$scope.filterApplied = false;
			if(sessionStorage.getItem('req_filter') !== null){
				sessionStorage.removeItem('req_filter');
				$window.location.reload();//forces page reload
			}
		}else{
			$scope.filterApplied = true;
		}
	}
	
	/* Generating the options of the Item select tag *
	
	lineDetails.getItems("../crud/read/getReqItemsList.php", {params: {station: userDetails.getStation()}}).then(res => $scope.items =res);
	
	$scope.changeItemFilter = function(){
		//toggleLoader("block");
		console.log($scope.item_name.value)
		$http.get("../crud/read/filterRequisitions.php", {params: {station: userDetails.getStation(), item: $scope.item_name.value}}).then(function(response){
			if(response.data.status === 1){
				$scope.requisitions = response.data.message;
				sessionStorage.setItem("req_filter", JSON.stringify(response.data.message));
				$timeout(function(){
					$scope.$apply();
				}, 1000);
				$scope.showNoItems = false;
				toggleLoader("none");
				document.getElementById("applyFilter_btn").innerHTML = "Remove Filter";
			}else{
				$scope.requisitions = undefined;
				$timeout(function(){
					$scope.$apply();
				}, 1000);
				$scope.showNoItems = true;
			}
			toggleLoader("none");
			console.log(response.data);
		});
	}
	
	if(sessionStorage.getItem('req_filter') !== null){
		document.getElementById("applyFilter_btn").innerHTML = "Remove Filter";
		toggleLoader("none");
		$scope.filterApplied = true;
		$scope.requisitions = JSON.parse(sessionStorage.getItem('req_filter'));
		console.log('done');
	}else{
		/*Generating requisitions list */
		$http.get("../crud/read/getRequisitions.php", {params: {station: userDetails.getStation()}}).then(function(response){
			console.log(response.data);if(response.data.status == 1){
				if(userDetails.getStation() === undefined){
					$scope.activeStation = false;
				}else{
					$scope.activeStation = true;
				}
				$scope.showNoItems = false;
				//angular.forEach(response.data.message, function(v){reqTotal = reqTotal + Number(v.amount)});
				$scope.requisitions = response.data.message;//response.data.userLevel = "Level3";console.log("Danke" ,$scope.requisitions);
				$scope.total = response.data.reqTotal;
				toggleLoader("none");
				console.log(response.data);
			}else if(response.data.status == 2){
				$scope.showNoItems = true;
				toggleLoader("none");console.log(response.data);
			}
		}, function(response){
			console.log(response.data);
		});
	//}
		
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
		}else{
			grandtotal = 0;
		}
		return grandtotal;
	}
	
	/* When user chooses item, apply the rate */
	$scope.reqChangeItem = function(row, index){
		lineDetails.checkItem(row, index, req_details, $scope.rows, 'req');
	}

	
	$scope.categoryChanged = function(){
		$scope.rows = [{ID:1, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false}];
		req_details.length = 0;
		if(userDetails.getUserLevel() === "Level1"){
			if($scope.category === "Drinks" /*|| $scope.category === undefined*/){
				$scope.showAmounts = false;
			}else{
				$scope.showAmounts = true;
			}
		}else{
			$scope.showAmounts = true;
		}
		
		if($scope.category === undefined){
			req_details.length = 0;console.log(req_details);
		}
	}
	
	/* Computing the Item total from Qty */
	$scope.reqComputeSubTotal = function(/*qty,item*/row, idx){
		if(userDetails.getUserLevel() === "Level1" && $scope.category === "Drinks"){
			if(row.qty !== null){
				if(row.qty > 0){
					req_details[idx].qty = row.qty;console.log(req_details);
				}else{
					alert("Qty should atleast be 1");
					row.qty = null;
				}
			}
		}else{
			lineDetails.checkQty(/*qty,item*/row, idx, req_details, 'req');
		}
	}
	
	$scope.updPurchAmnt = function(row, idx){
		if(isNaN(row.purchaseAmount)){
			alert("Please enter only digits");
			row.purchaseAmount = null;
		}else if (Number(row.purchaseAmount) <= 0){
			alert("Amount should be greater than 0");
			row.purchaseAmount = null;
		}else if(Number(row.purchaseAmount) == row.total){
			alert("Amount entered is same as Total and will be ignored");
			row.purchaseAmount = null;
		}else{
			if(row.purchaseAmount !== "" /*|| amnt === '0'*/){
				req_details[idx].purchaseAmount = Number(row.purchaseAmount);
			}
		}
	}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getReqItemsList.php", {params: {station: userDetails.getStation()}}).then(res => $scope.items =res);
	/*$http.get("../crud/read/getReqItemsList.php").then(function(response){
		$scope.items = response.data;
	});*/
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [{ID:1, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false}];
	
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
		
		let form_values = {category:$scope.category, station:$scope.station, requisitiontype: type(), details:req_details, userID: userDetails.getUserID()};
		console.log(form_values);
		
		document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
		document.getElementsByClassName("save_btn")[0].style.cursor = "not-allowed";
		$scope.category = undefined;
		$scope.rows = [{ID:1, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false}];
		
		$http.post("../crud/create/add_requisition.php", form_values).then(function(response){
			httpResponse.success(1, response.data.message);console.log(response.data);
			
			//console.log(response.data);
			exitEditMode("reqs_btn");
		}, function(response){
			httpResponse.error(0, response.data);	
		});
	}
	
	function type(){
		if($scope.category === "Eats" || $scope.category === "Kitchen"){
			return "External";
		}else{
			if(userDetails.getUserLevel() === "Level1"){
				return "Internal";
			}else{
				return "External";
			}
		}
	}

});

function showAmountsDesc(userLevel, category){
	if(userLevel === "Level1"){
		if(category === "Drinks" /*|| category === undefined*/){
			return false;
		}else{
			return true;
		}
	}else{
		return true;
	}
}

theApp.controller("edit_requisitionCtlr", function($scope, $http, $routeParams, $timeout, $q, httpResponse, lineDetails, userDetails){
	let userID = userDetails.getUserID();
	let editreq_details = [];
	let deleted_req_lines = [];
	let promisesArr = [];
	
	$http.post("../crud/read/getRequisition.php", {reqNo: $routeParams.reqNo, type: $routeParams.type}).then(function(response){
		$scope.station = response.data.requisition[0].Station;
		$scope.category = response.data.requisition[0].Category;
		angular.forEach(response.data.req_details, function(v)  {
			v.deleted = false;
			
			if(Number(v.qty) > 1){
				v.UnitQty = `${v.UnitQty}S`;
			} 	
		})//Add deleted property for toggling deleted class in ngRepeat
		$scope.requisition_items = response.data.req_details;
		$scope.getGrandTotal = gTotal(response.data.req_details);
		if(userDetails.getUserLevel() === "Level1"){
			if($scope.category === "Drinks" /*|| $scope.category === undefined*/){
				$scope.showAmounts = false;
			}else{
				$scope.showAmounts = true;
			}
		}else{
			$scope.showAmounts = true;
		}
		//$scope.showAmounts = function() {showAmountsDesc(userDetails.getUserLevel(), $scope.category);}
		console.log(response.data);
	}, function(response){
		console.log(response.data);
	});
	
	/* When user chooses item, apply the rate */
	$scope.reqChangeItem = function(row, index){ lineDetails.checkItem(row, index, editreq_details,	$scope.rows, 'req');}

	/* Computing the Item total from Qty */
	$scope.reqComputeSubTotal = function(/*qty,item*/row, idx){
		if(userDetails.getUserLevel() === "Level1" && $scope.category === "Drinks"){
			if(row.qty !== null){
				if(row.qty > 0){
					editreq_details[idx].qty = row.qty;console.log(editreq_details);
				}else{
					alert("Qty should atleast be 1");
					row.qty = null;
				}
			}
		}else{
			lineDetails.checkQty(/*qty,item*/row, idx, editreq_details, 'req');
		}
	}
	
	$scope.updPurchAmnt = function(row, idx){
		if(isNaN(row.purchaseAmount)){
			alert("Please enter only digits");
			row.purchaseAmount = null;
		}else if (Number(row.purchaseAmount) <= 0){
			alert("Amount should be greater than 0");
			row.purchaseAmount = null;
		}else if(Number(row.purchaseAmount) == row.total){
			alert("Amount entered is same as Total and will be ignored");
			row.purchaseAmount = null;
		}else{
			if(row.purchaseAmount !== "" /*|| amnt === '0'*/){
				req_details[idx].purchaseAmount = Number(row.purchaseAmount);
			}
		}
	}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getReqItemsList.php", {params: {station: userDetails.getStation()}}).then(res => $scope.items =res);
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [/*{ID:1}*/];
	
	/* Function to add a row to the orders details */
	$scope.addRow = /*() => lineDetails.addRow_edit($scope.rows, $scope.showEditDetails, $scope.requisition_items);*/function(){
		let counter = ($scope.rows.length + 1) + $scope.requisition_items.length;
		if(!$scope.showEditDetails){
			$scope.showEditDetails = true;
			$scope.rows.push({ID:counter, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false});
		}else{
			$scope.rows.push({ID:counter, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false});
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
			deleted_req_lines.push({detailsID/*, index*/});
		}else{
			$scope.requisition_items[index].deleted = false;
			deleted_req_lines.splice(index,1);
		}
		
		if(deleteAllItems()){
			alert("Looks like you're deleting all items, if you continue to submit this requisition will be deleted");
		}
		//console.log(deleted_order_lines, $scope.order_items[index]);
	}
	
	function deleteAllItems(){
		if(deleted_req_lines.length === $scope.requisition_items.length && editreq_details.length === 0){
			return true;
		}else{
			return false;
		}
	}
	
	//Delete Order Line Promise
	function deletePromise(_data){
		return $http.delete("../crud/delete/deleteReqLine.php", {data:_data});
	}
	
	//Add Order Line Promise
	function addPromise(_data){console.log(_data);
		return $http.post("../crud/create/addReqLine.php", _data);
	}
	
	function collectPromises(){
		let collected = false;
		
		if(!deleteAllItems()){
			if(deleted_req_lines.length !== 0){
				promisesArr.push(deletePromise(/*{deletedLines: */deleted_req_lines));
				collected = true;
			}
			
			if(editreq_details.length !== 0){
				let reqType;
				if(userDetails.getUserLevel() === "Level1"){
					 if($scope.category === "Drinks"){
							reqType = "intDrinks";
						}else if($scope.category === "Eats"){
							reqType = "intEats";
						}else if($scope.category === "Kitchen"){
							reqType = "intKitchen";
						}
					}else{
						if($scope.category === "Drinks"){
							reqType = "extDrinks";
						}else if($scope.category === "Eats"){
							reqType = "extEats";
						}else if($scope.category === "Kitchen"){
							reqType = "extKitchen";
						}
					}
				promisesArr.push(addPromise({reqNo: $routeParams.reqNo, reqType: reqType, addedLines: editreq_details}));
				collected = true;
			}
		}else{
			promisesArr.push($http.delete("../crud/delete/deleteReq.php", {data:{reqNo: $routeParams.reqNo, userID: userID}}));
			//console.log(JSON.stringify({reqNo: $routeParams.reqNo}));
			collected = true;
		}
		
		return collected;
	}
	
	$scope.validate = function (){ 
		if(collectPromises()){
			document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			document.getElementsByClassName("save_btn")[0].style.cursor = "not-allowed";
			
			$q.all(promisesArr).then(function(response){
				if(collectPromises.length === 2){
					if(response[0].data.status === 1 && response[1].data.status === 1){
						httpResponse.success(1, "Updated Succesfuly");
					}else if(response[0].data.status === 0 || response[1].data.status === 0){
						httpResponse.success(0, "One or both operations failed, check and try again");
					}
				}else{//console.log(response[0].data);
					if(response[0].data.status === 1){
						if(!deleteAllItems()){
							httpResponse.success(1, "Updated Succesfuly");
						}else{
							httpResponse.success(1, "Deleted Succesfuly, please wait...");
							document.body.style.cursor = "wait";
							$timeout(function(){
								//wait for the httpResponse above to finish and return to the requisitions list
								document.getElementById("reqs_btn").click();
								document.body.style.cursor = "auto";
							}, 7000)
						
						}
					}else{
						httpResponse.success(0, "The operation failed, Please try again");console.log(promisesArr,response[0].data);
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

theApp.controller("recv_requisitionCtlr", function($scope, $http, $routeParams, httpResponse, userDetails){
	let recvd_items = [];
	let given_items = [];
	
	$scope.showGiven = function(){
		//if(userDetails.getUserLevel() === "Level1"){
			if($routeParams.category === "Drinks"){
				return true;
			}else{
				return false;
			}
		//}
	}
	
	$scope.showQtyGiven = function(){
		//if(userDetails.getUserLevel() === "Level1"){
			if($routeParams.category === "Drinks"){
				return true;
			}else{
				return false;
			}
		//}
	}
	
	$scope.showFinalAmount = function(){
		if(userDetails.getUserLevel() === "Level1"){
			if($routeParams.category === "Drinks"){
				return false;
			}else{
				return true;
			}
		}else{
			if($routeParams.category === "Eats"){
				return false;
			}else{
				if($scope.reqType == "Internal"){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	$scope.showRcvd = function(){
		if(userDetails.getUserLevel() === "Level1"){
			if($routeParams.category === "Eats"){
				return true;
			}else{
				return true;
			}
		}else{
			if($routeParams.category === "Eats"){
				return false;
			}else{
				if($scope.reqType == "Internal"){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	$scope.showQtyRcvd = function(){
		if(userDetails.getUserLevel() === "Level1"){
			if($routeParams.category === "Eats"){
				return true;
			}else{
				return true;
			}
		}else{
			if($routeParams.category === "Eats"){
				return false;
			}else{
				if($scope.reqType == "Internal"){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	
	//if($routeParams.category === "Eats" || $routeParams.category === undefined){
	$http.post("../crud/read/getRequisition.php", {reqNo: $routeParams.reqNo, type: $routeParams.type}).then(function(response){
		$scope.station = response.data.requisition[0].Station;
		$scope.category = response.data.requisition[0].Category;
		$scope.reqType = response.data.requisition[0].RequisitionType;console.log(response.data.req_details);
		if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
		angular.forEach(response.data.req_details, function(v)  {//Add deleted property for toggling deleted class in ngRepeat
			if(v.isChecked === '0'){
				
				v.isChecked = false;
				v.isDisabledRcv = false;
			}else{
				v.qty_recvd = Number(v.qty_recvd);
				v.isChecked = true;
				v.isDisabledRcv = true;
			}
			
			if(Number(v.qty) > 1){
				v.UnitQty = `${v.UnitQty}S`;
			}
			//v.qty_recvd = null;
		})
		}else{
			if(userDetails.getUserLevel() !== "Level1"){
				//$scope.showGiven = false;
				angular.forEach(response.data.req_details, function(v)  {
					if(v.isGiven === '0' || v.isGiven === null){
						v.isGiven = false;
						//v.isDisabled = false;
					}else{
						v.isGiven = true;
						//v.isDisabled = true;
						v.QtyGiven = Number(v.QtyGiven);
						v.qty_recvd = Number(v.qty_recvd);
					}
					
					if(v.isChecked === '0'){
						v.isDisabledRcv = false;
						v.isChecked = false;
					}else{
						v.isChecked = true;
						v.isDisabledRcv = true;
					}
				});console.log(response.data.req_details);
			}else{
				//$scope.showGiven = true;
				angular.forEach(response.data.req_details, function(v)  {
					if(v.isGiven === '0' || v.isGiven === 'null'){
						v.isGiven = false;
						v.isDisabledGvn = true;
						//v.isDisabledRcv = true;
					}else{
						v.isGiven = true;
						v.isDisabledGvn = true;
						v.QtyGiven = Number(v.QtyGiven);
						v.qty_recvd = Number(v.qty_recvd);
					}
					
					if(v.isRecieved === '0'){
						v.isDisabledRcv = false;
						v.isChecked = false;
					}else{
						v.isChecked = true;
						v.isDisabledRcv = true;
					}
				});console.log(response.data.req_details);
			}
			
		}
		
		if(userDetails.getUserLevel() === "Level1"){
			$scope.state = "Recieve Items";
			$scope.showApprove = false;
		}else{
			$scope.state = "Approve Requisition";
			$scope.showApprove = true;
			if(response.data.requisition[0].PurchaseStatus === "Approved"){
				$scope.approve = "Approved";
			}
		}
		
		$scope.requisition_items = response.data.req_details;//console.log(response.data.req_details);
	},function(response){
		console.log(response.data);
	});
	/*}else{
		$scope.showGiven = true;
	}*/
	$scope.recieveItem = function(row, index){//Entire row is brought to access the values of the object as needed (Consider this unlike in Create & Edit)
		//let qty_recvd = document.getElementsByClassName("qty_recvd");
		if(userDetails.getUserLevel() === "Level1"){	
			if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
				if(row[index].isChecked){
					row[index].qty_recvd = Number(row[index].qty);
					//recvd_items.push({Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd});
					recvd_items[index] = {Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd};
					//console.log("Danku",recvd_items);
				}else{
					row[index].qty_recvd = null;
					removeItem(recvd_items, index);
					//console.log("Ooops", recvd_items);
				}
			}else{
				if(row[index].isChecked){
					if(!row[index].isGiven){
						alert("Item is not yet given");
						row[index].isChecked = false;
					}else{
						row[index].qty_recvd = row[index].QtyGiven;
						//recvd_items.push({Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd});
						recvd_items[index] = {Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd};
						//console.log("Danku",recvd_items);
					}
				}else{
					row[index].qty_recvd = null;
					removeItem(recvd_items, index);
				}
			}
		}else{
			if(row[index].isChecked){
				row[index].qty_recvd = Number(row[index].qty);
				//recvd_items.push({Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd});
				recvd_items[index] = {Details_No: row[index].DetailsNo, RecievedStatus: 1, QtyRecieved: row[index].qty_recvd};
				//console.log("Danku",recvd_items);
			}else{
				row[index].qty_recvd = null;
				removeItem(recvd_items, index);
				//console.log("Ooops", recvd_items);
			}			
		}
	}
	
	let items_changed = [];
	$scope.updQtyRcvd = function(row, index){
		let qty = row[index].qty_recvd;
		
		if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
			if(qty === 0){
				alert("Cannot recieve 0 items, Recv'd will be unchecked instead");
				row[index].isChecked = false;
				removeItem(recvd_items, index);
				row[index].qty_recvd = null;
				row[index].FinalAmount = null;
			}else if(qty === null && row[index].isChecked){
				alert("Quantity recieved is required, Recv'd will be unchecked instead");
				row[index].isChecked = false;
				removeItem(recvd_items, index);
				row[index].qty_recvd = null;
				row[index].FinalAmount = null;
			}else if(qty !== null && row[index].isChecked && !row[index].isDisabled){
				if(qty !== Number(row[index].qty)){
					row[index].FinalAmount = qty * Number(row[index].rate);
					if(recvd_items.length === 1){
						recvd_items[0].QtyRecieved = qty;
						recvd_items[0].FinalAmount = row[index].FinalAmount;
						items_changed.push(row[index].item);
					}else{
						recvd_items[index].QtyRecieved = qty;
						recvd_items[index].FinalAmount = row[index].FinalAmount;
						items_changed.push(row[index].item);
					}
				}
			}
		}else{
			if(userDetails.getUserLevel() === "Level1"){	
				if(!row[index].isDisabledRcv /*|| row[index].isGiven*/){
					alert("Qty Recieved should equal to Qty Given, adjust Qty Given instead");
					row[index].qty_recvd = row[index].QtyGiven;
				}
			}else{
				if(Number(row[index].isChecked)){
					if(qty !== null /* && !row[index].isDisabled*/){
						row[index].FinalAmount = qty * Number(row[index].rate);
						if(recvd_items.length === 1){
							recvd_items[0].QtyRecieved = qty;
							recvd_items[0].FinalAmount = row[index].FinalAmount;
						}else{
							recvd_items[index].QtyRecieved = qty;
							recvd_items[index].FinalAmount = row[index].FinalAmount;
						}
					}//console.log(row[index].isChecked);
				}else{
					alert("Mark item as recieved before you continue");
					row[index].qty_recvd = 0;
				}
			}
		}
		//console.log(qty, index, recvd_items,row[index].qty_recvd);
	}
	
	$scope.updFinAmnt = function(row, index){
		let finAmnt = Number(row[index].FinalAmount);
		let qty_rqstd = row[index].qty;
		let qty_recvd = row[index].qty_recvd;
		
		if(/*finAmnt != null ||*/ !isNaN(finAmnt)/* && finAmnt !== 0*/){
			if(recvd_items.length === 1){
				recvd_items[0].FinalAmount = finAmnt;
				items_changed.push(row[index].item);
			}else{console.log(recvd_items[index], index)
				recvd_items[index].FinalAmount = finAmnt;
				items_changed.push(row[index].item);
			}
		}else /*if(finAmnt === 't')*/{
			alert("Please enter only digits");
			if(recvd_items.length === 1){
				delete recvd_items[0].FinalAmount;
			}else{
				delete recvd_items[index].FinalAmount;
			}
		}
		console.log(recvd_items, /*recvd_items[1].FinalAmount,*/ finAmnt,index);
	}
	
	$scope.giveItem = function(row, index){//Entire row is brought to access the values of the object as needed (Consider this unlike in Create & Edit)
		
		if(row[index].isGiven){
			row[index].QtyGiven = Number(row[index].qty);
			given_items.push({DetailsNo: row[index].DetailsNo, GivenStatus: 1, QtyGiven: row[index].QtyGiven});
			console.log("Danku",given_items);
		}else{
			row[index].QtyGiven = null;
			removeItem(given_items, index);
			console.log("Ooops", given_items);
		}
	}
	
	$scope.updQtyGvn = function(row, index){
		if(userDetails.getUserLevel() !== "Level1"){
		let qty = row[index].QtyGiven;
		
		if(qty === 0){
			alert("Cannot give 0 items, Given will be unchecked instead");
			row[index].isGiven = false;
			removeItem(given_items, index);
			row[index].QtyGiven = null;
		}else if(qty === null && row[index].isGiven){
			alert("Quantity given is required, Given will be unchecked instead");
			row[index].isGiven = false;
			removeItem(given_items, index);
			qty = null;
		}else if(qty !== null && row[index].isGiven && !row[index].isDisabled){
			if(given_items.length === 1){
				given_items[0].QtyGiven = qty;
			}else{
				given_items[index].QtyGiven = qty;
			}
		}
		
		console.log(qty, index, given_items,row[index].QtyGiven);
		}
	}
	
	$scope.validate = function(){console.log(postData());
		if(approve()){
			if(checkArrSize()){
				if(confirmEditedItems(items_changed)){
					$http.post(postUrl(),postData()).then(function(response){
						if(response.data.status = 1){
							httpResponse.success(1, response.data.message);
						}else{
							httpResponse.success(0, response.data.message);
						}
						document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
						console.log(response.data);
					}, function(response){console.log(postData());
						httpResponse.error(0, response.data);	
					});//console.log(postUrl(), postData());
				}
			}
		}
	}
	
	function confirmEditedItems(arr){
		let isConfirmed;
		if(arr.length > 0){
			arr = [... new Set(arr)]//removes duplicate values
			if(confirm(`You have changed the following items:\n \n${arr.toString()}.\n \nDo you want to continue?`)){
				isConfirmed = true; 
			}else{
				isConfirmed = false;
			}
		}else{
			isConfirmed = true; 
		}
		
		return isConfirmed;
	}
	
	function removeItem(arr, idx){
		if(arr.length === 1){
			arr.splice(0,1);
		}else{
			arr.splice(idx,1);
		}
	}
	
	function approve(){
		let approved;
		if(userDetails.getUserLevel() === "Level1"){// assumed regular user is ready to recive items of already approved requisition
			approved = true;
		}else{
			if($scope.approve === undefined /*&& checkArrSize()*/){console.log($scope.approve);
				approved = false;
				alert("Requisition is not Approved, approve to contine")
			}else{
				approved = true;
			}
		}
		return approved;
	}
	
	function checkArrSize(){
		if(userDetails.getUserLevel() === "Level1"){
			if(recvd_items.length === 0){
				alert("No Item has been marked as recieved");
			}else{
				return true;
			}
		}else{
			//if($scope.category === "Drinks"){
				if($scope.reqType == "Internal"){
					if(given_items.length === 0){
						alert("No Item has been marked as given");
					}else{
						return true;
					}
				}else{
					if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
					 	return true;	
					 }else{
						if(recvd_items.length === 0){
							alert("No Item has been marked as recived");
						}else{
							return true;
						}
					}
				}
			/*}else{
				return true;
			}*/
		}
	}
	
	function postData(){
		let _data;
		if(userDetails.getUserLevel() === "Level1"){
			_data = {reqNo: $routeParams.reqNo, recvd_items: recvd_items};
		}else{
			if($scope.reqType == "Internal"){
			if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
				_data = {reqNo: $routeParams.reqNo, PurchaseStatus: $scope.approve};
			}else{
				if($scope.approve !== undefined){
					_data = {reqNo: $routeParams.reqNo, PurchaseStatus: $scope.approve, given_items: given_items};
				}else{
					_data = {given_items: given_items};
				}
			}
			}else{
				if($routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
				_data = {reqNo: $routeParams.reqNo, PurchaseStatus: $scope.approve};
			}else{
				if($scope.approve !== undefined){
					_data = {reqNo: $routeParams.reqNo, PurchaseStatus: $scope.approve, recvd_items: recvd_items};
				}else{
					_data = {recvd_items: recvd_items};
				}
			}
			}
		}
		return _data;
	}
	
	function postUrl(){
		if(userDetails.getUserLevel() === "Level1"){
			return "../crud/update/recieveItems.php";
		}else{
			if($scope.reqType == "Internal" || $routeParams.category === "Eats" || $routeParams.category === "Kitchen"){
				return "../crud/update/approve_giveItems.php";
			}else{
				return "../crud/update/recieveItems.php";
			}
			
		}
	}
});

theApp.controller("view_requisitionCtlr", function($scope, $http, $routeParams, userDetails,lineDetails){
	let edited_rows = [];
	
	if(userDetails.getUserLevel() === "Level3"){
		$scope.isAdmin = true;
	}else{
		$scope.isAdmin = false;
	}
	
	$http.post("../crud/read/getViewRequisition.php", {reqNo: $routeParams.reqNo, type: $routeParams.type}).then(function(response){
		if(response.data.status == 1){
			$scope.station = response.data.requisition[0].Station;
			$scope.category = response.data.requisition[0].Category;
			$scope.reqType = response.data.requisition[0].RequisitionType;console.log(response.data.req_details);
			if($scope.reqType == 'Internal'){
				$scope.hideAmounts = true;
			}else{
				$scope.hideAmounts = false;
			}
			
			$scope.dateCreated = response.data.requisition[0].PurchaseDate;
			$scope.staff = response.data.requisition[0].FirstName;
			$scope.status = response.data.requisition[0].PurchaseStatus;
			
			angular.forEach(response.data.req_details, function(v){
				if($scope.reqType === "External"){
					v.isGiven = true;
					//v.isDisabled = true;
					v.QtyGiven = Number(v.qty);
				}else{
					if(v.isGiven == 1){
						v.isGiven = true;
					}else{
						v.isGiven = false;
					}
				}				
			});
			
			angular.forEach(response.data.req_details, function(v){
				if(v.isRecieved == 1){
					v.isRecieved = true;
				}else{
					v.isRecieved = false;
				}				
			});
			
			$scope.requisition_items = response.data.req_details;
			$scope.closedDate = response.data.req_details[0].lastRecieved;
			$scope.getGrandTotal = gTotal(response.data.req_details);
		}else{
			console.log(response.data);
		}
	}, function(response){
		console.log(response.data);
		
	});
	
	$scope.updQtyGvn = function(row, index){
		if(!isNaN(row.QtyGiven)){
			if($routeParams.type === "Internal"){
				if(row.isGiven){
					if(row.isRecieved){
						row.qty_recvd = row.QtyGiven;
						edited_rows[index] = {Details_No:row.DetailsNo, QtyGiven: row.QtyGiven, QtyRecieved: row.QtyGiven};
						//console.log(edited_rows);
					}
				}else{
					alert("Item is not yet given");
				}
			}
		}else{
			alert("Please enter numbers only");
			row.QtyGiven = null;
		}
	}
	
	$scope.recieveItem = function(row, index){
		if(!row.isRecieved){
			row.qty_recvd = null;
			edited_rows[index] = {Details_No:row.DetailsNo, RecievedStatus: 0};
		}else{
		//Does opposite if item is not recieved
			if(confirm("Sorry, cannot recieve item from here. Do you want to undo the previous action?")){
				row.isRecieved = true;
				row.qty_recvd = Number(row.qty);
				edited_rows.splice(index, 1);
			}else{
				row.isRecieved = false;
				row.qty_recvd = null;
			}
		}
		
		//console.log(row,edited_rows);
		
	}
	
	$scope.updQtyRcvd = function(row, index){
		let qty = Number(row.qty_recvd);
		//if(qty != row.QtyGiven){
			if($routeParams.type === "External"){
				if(!row.isRecieved){
					alert("Mark item as recieved before you continue");
					row.qty_recvd = null;
				}else{
					if(qty === 0){
						alert("Cannot recieve 0 items, Recv'd will be unchecked instead");
						row.isRecieved = false;
						edited_rows[index] = {Details_No:row.DetailsNo, RecievedStatus: 0};
						row.qty_recvd = null;
						row.FinalAmount = null;//console.log(edited_rows);
					}else if(isNaN(qty)){
						alert("Please enter only numbers");
						row.isRecieved = false;
						edited_rows[index] = {Details_No:row.DetailsNo, RecievedStatus: 0};
						row.qty_recvd = null;
						row.FinalAmount = null;//console.log(edited_rows);
					}else if(qty !== null && row.isRecieved){
						row.FinalAmount = qty * Number(row.rate);
						edited_rows[index] = {Details_No:row.DetailsNo, RecievedStatus: 1, QtyRecieved: qty, FinalAmount: row.FinalAmount};
						//console.log(edited_rows);
					}
				}
			}else{
				alert("Qty Recieved should equal to Qty Given, adjust Qty Given instead");
				row.qty_recvd = row.QtyGiven;
			}
		/*}else{
			if(Number(row.qty_recvd) != Number()
		}*/
	}
	
	$scope.updFinAmnt = function(row, index){
		let finAmnt = Number(row.FinalAmount);
				
		if(!isNaN(finAmnt)){
			edited_rows[index] = {Details_No:row.DetailsNo, /*QtyRecieved:Number(row.qty_recvd),*/ FinalAmount: row.FinalAmount};
			//console.log(edited_rows);
		}else{
			alert("Please enter only digits");
			row.FinalAmount = null;
		}
	}
	
	let deleted_rows = [];
	$scope.deleteLineItem = function(row, index){
		deleted_rows.push({detailsID: row.DetailsNo});
		if(confirm(`Are you sure you want to delete [${row.item}]?`)){
			$http.delete("../crud/delete/deleteReqLine.php", {data:deleted_rows}).then(function(response){
				if(response.data.status == 1){
					alert(response.data.message);console.log(response.data.message);
				}else{
					alert(response.data.message);console.log(response.data);
				}
			});
		}
		//console.log(DetailsNo);
	}
	
	$scope.validate = function(){
		if(edited_rows.length == 0){
			alert("Nothing was edited");console.log('edited')
		}else{
			angular.forEach(edited_rows, function(k,v){
				//if(v == undefined){
					console.log(k,v);
					//edited_rows.splice(k,1);
				//}
			});
			$http.post("../crud/update/setRequisition.php", {userID:userDetails.getUserID(), reqType: $scope.reqType, reqDetails: edited_rows}).then(function(response){
				if(response.data.status == 1){
					alert(response.data.message);
					console.log(response.data);
				}else{
					alert(response.data.message);
					console.log(response.data);
				}
			}, function(response){
				console.log(response.data);
			});
			console.log(edited_rows);
		}
	}
});



