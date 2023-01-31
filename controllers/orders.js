theApp.controller("ordersCtlr", function($scope, $http, userDetails){
	$scope.ID = userDetails.getUserID();
	$http.get("../crud/read/getOrders.php", {params: {station: userDetails.getStation()}}).then(function(response){
		$scope.orders = response.data;/* Generating orders list */
		//console.log(response.data);
	},function(response){
		console.log(response.data);
	});

});

theApp.controller("create_orderCtlr", function($scope, $timeout, $http, userDetails, lineDetails){

	$scope.station = userDetails.getStation();
	$scope.showEditBtn = false;
	/* When user chooses delivery point */
	$scope.changeTo = function(){
		if($scope.to === "Table"){
			$scope.show = true;
			tables().then(res => $scope.delvPnts = res);
		}else if($scope.to === "Room"){
			$scope.show = true;
			$scope.delvPnts = rooms();
		}else{
			$scope.show = false;
			$scope.delvPnts = undefined;
		}
	}

	let order_details = [];//to contain order details for form submission

	/* Generate Order details Grand Total */
	$scope.getGrandTotal = function() {
		let grandtotal = 0;
		if(order_details.length !== 0){
			for(let y = 0; y < order_details.length; y++){
				grandtotal += order_details[y].subtotal ;
			}
		}
		return grandtotal.toLocaleString();
	}

	/* When user chooses item, apply the rate */
	//$scope.itemRate = function(item, index){ applyRate(item, index, order_details,$scope);}
	$scope.changeItem = function(row, index)  {console.log(row); lineDetails.applyRate(row, index, order_details, $scope.rows);}
	
	/* Computing the Item total from Qty */
	//$scope.computeSubTotal = function(qty,item, idx){ computeSubTotal(qty,item, idx, order_details); }
	$scope.computeSubTotal = function(/*qty,item*/row, idx) {lineDetails.computeSubTotal(/*qty,item*/row, idx, order_details);} 

	$scope.q = function(){
		console.log($scope.delv_point)
	}
	
	/* Generating the options of the Delivery points select tag */
	
	function tables(){
		return $http.get("../crud/read/getTableDelvPoints.php").then(function(response){
			//tables = () => response.data.message;//Arrow function declaration
			//console.log(tables(), "Danku");
			return response.data.message;
		},function(response){
			console.log(response.data);
		});
		
	}

	function rooms(){
		room = [
			
				{pntName: "Braxton"}, 
				{pntName: "Parkers"}, 
				{pntName: "Nic"}
			];
		return room;
	}


	/* Generating the options of the Item select tag */
	//$scope.items = lineDetails.getItems("../crud/read/getOrdItemsList.php");
	lineDetails.getItems("../crud/read/getOrdItemsList.php").then(res => $scope.items =res);
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [{ID:1, item: undefined, qty:null, rate:null, total:null, itemSelected: false}];

	/* Function to add a row to the orders details */
	$scope.addRow = () => {lineDetails.addRow_create($scope.rows, order_details);}

	/* Function to reomve a row from the orders details */
	$scope.removeRow = (index) => lineDetails.removeRow_create($scope.rows, index, "Order must have atleast one item", order_details);

	/* Validate Form Data and submit */
	$scope.validate = function (){

		let form_values = {station:$scope.station, to:$scope.to, delv_point:$scope.delv_point, details:order_details, userID: userDetails.getUserID()};
		
		$http.post("../crud/create/add_order.php", form_values).then(function(response){
			
			toggleLoader("block");
			//$scope.showEditBtn = true;
			$scope.ordNo = response.data.ordNo;
			
			$timeout(function(){
				if(response.data.status === 1){
					$scope.showEditBtn = true;
					displayResponseBox(true, response.data.message);
				}else{
					displayResponseBox(false, response.data.message);
				}
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
			}, 2000);
			//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			
		}, function(response){
			toggleLoader("block");
			
			$timeout(function(){
				displayResponseBox(false, response.data);
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
			}, 2000);
			
		});
	}

});

/*function getItems(http){
	return http.get("../crud/read/getOrdItemsList.php").then(function(response){
		return response.data;
	});/*fetch("../crud/read/getOrdItemsList.php").then(function(response){
		return response.json();
	}).then(function(data){
		return data.message;
	});*/
//}

/*function applyRate(x, idx, line_details_arr, scope){
	//console.log(x, idx);
	let qty_input = document.getElementsByClassName("qty_input");
	let rate_input = document.getElementsByClassName("rate_input");
	let total_input = document.getElementsByClassName("total_input");
	for(let i = 0; i<rate_input.length; i++){
		if(idx === i){
			if(x !== undefined){
				rate_input[i].value = x.rate;
				
				line_details_arr[idx] = {pdtNo:x.value};//adding the order details' objects to array by the current index
				
				//Apply the total if the qty field is already filled
				if(qty_input[i].value !== undefined){
					let qty = qty_input[i].value;
					scope.computeSubTotal(qty,x,idx);
				}
			}else{
				qty_input[i].value = "";
				rate_input[i].value = "";
				total_input[i].value = "";
			}
		}
	}
	
}

function computeSubTotal(qty,item, idx, line_details_arr){
	let total_input = document.getElementsByClassName("total_input");
	if(item !== undefined && qty !== "" ){
		for(let x = 0; x < total_input.length; x++){
			if(idx === x){
				total_input[x].value = qty * item.rate;
				
				//adding the requisition details' objects' properties by the current index
				line_details_arr[idx].qty = qty;
				line_details_arr[idx].rate = item.rate;
				line_details_arr[idx].subtotal = qty * item.rate;
				console.log(line_details_arr);
			}
		}
	}
}*/

let ordPreptime = (arr) => {
	let newarr = [];
	for(let v of arr){
		newarr.push(Number(v.PrepTime));
	}
	return Math.max(...newarr);
}

theApp.controller("edit_orderCtlr", function($scope, $timeout, $interval, $http, $routeParams, $q, userDetails, httpResponse, lineDetails){
	let odrStartTime;
	let ordMaxTime;
	let ordStatus;
	let editorder_details = [];//to contain order details for form submission
	let deleted_order_lines = [];//to contain order details deleted for form submission
	let promisesArr = [];
	
	if(userDetails.getUserLevel() === "Level2"){
		$scope.showVerify = true;
	}
	
	/*Pre-select icon class for the delete action of order details*/
	//let del_icon = "fa fa-trash";
	//let undo_icon = "fa fa-undo";
	//$scope.iconClass = function(id){ return del_icon;}
	//console.log(icon.length);//.classList.add(del_icon);
	
	$http.post("../crud/read/getOrder.php", {ordNo: $routeParams.ordNo}).then (function(response){
		
		$scope.ordNo = $routeParams.ordNo;
		$scope.station = response.data.order[0].Station;
		$scope.to = response.data.order[0].To;
		$scope.delv_point = response.data.order[0].DeliveryPoint;
		odrStartTime = response.data.order[0].OrderDate;
		ordMaxTime = ordPreptime(response.data.ord_details);
		ordStatus = response.data.order[0].OrderStatus;
		angular.forEach(response.data.ord_details, function(v)  {v.deleted = false})//Add deleted property for toggling deleted class in ngRepeat
		$scope.order_items = response.data.ord_details;//edit_ordersPage[0].order_items;
		$scope.getGrandTotal = gTotal(response.data.ord_details);
		console.log($scope.order_items);
		if(ordStatus === "Pending"){
			$scope.showDeliverBtn = true;
		}
	}, function(response){
		console.log(response.data);
	});
	
	
	$scope.deliver = function(){
		stop_timer();
		document.getElementById("order_status").style.display = "none";
		/*var loader = document.getElementById("loader");
		loader.style.display = "block";*/
		toggleLoader("block");
		$http.post("../crud/update/setOrder.php", {orderstatus: "Delivered", ordNo: $routeParams.ordNo}).then(function(response){
			
			$timeout(function(){
				if(response.data.status === 1){
					$scope.showDeliverBtn = false;
					displayResponseBox(1, "Order is marked as Delivered");
				}else{
					displayResponseBox(0, response.data.message);
				}
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
			}, 2000);
				
			console.log(response.data);
		},function(response){
			console.log(response.data);
		});
		
		/*$timeout(function(){
			loader.style.display = "none";
			var response_box = document.getElementById("response_box");
			response_box.innerHTML = "Order is marked as Delivered";
			response_box.style.display = "block";
			response_box.style.backgroundColor = "green";
			document.getElementsByClassName("deliver_btn_box")[0].style.display = "none";
			
			//Fadeout response_box after 4sec
			$timeout(fadeout, 4000);
		}, 2000);*/
	}
	
	
	function timer(){
		
		/*stop_timer();*/
		if(ordStatus === "Pending"){
			if(ordMaxTime !== 0){//When order items dont require preparation time e.g drinks
				var start_time = new Date(/*"12/31/2022 17:52:58"*/odrStartTime);
				let start_time_in_secs = Math.floor(start_time.getTime()/1000);
				
				var curr_time = new Date();
				let curr_time_in_secs = Math.floor(curr_time.getTime()/1000);
				
				var lead_time = ordMaxTime;
				let lead_time_in_secs = lead_time * 60;
				
				var end_time = start_time.getMinutes() + lead_time;
				let end_time_in_secs = start_time_in_secs + lead_time_in_secs;
				
				var time_left_mins = end_time - curr_time.getMinutes();
				var start_time_secs = start_time.getSeconds();
				if(start_time_secs == 0){var end_time_secs = 59}else{var end_time_secs = start_time_secs + (59 - start_time_secs);}
				var time_left_secs = end_time_secs - curr_time.getSeconds();
				var warning_time = lead_time * 0.5;
				var critical_time = lead_time * 0.25;
				
				if(end_time_in_secs > curr_time_in_secs){
					var order_time_el = document.getElementById("order_time");
					if(time_left_mins <= warning_time && time_left_mins > critical_time){
						if(order_time_el.className !== "order_time_warning" || order_time_el.className === " "){
							order_time_el.className = "order_time_warning";
						}
						$scope.ordertime = time_left_mins + " mins";
					}
					/*if(time_left_mins == critical_time){
						order_time_el.style.color = "red";
						$scope.ordertime = time_left_mins + " mins";
					}*/
					if(time_left_mins < critical_time){
						//if(order_time_el.className !== "order_time_critical" /*|| order_time_el.className === " "*/){
							order_time_el.className = "order_time_critical";
						//}
						$scope.ordertime = time_left_mins + " mins";
					}
					if(time_left_mins > 1){
						$scope.ordertime = time_left_mins + " mins";
					}else if(time_left_mins === 1){
						$scope.ordertime = time_left_mins + " min";
					}else{
						$scope.ordertime = time_left_secs + " secs";
					}
				}else{
					stop_timer();
					document.getElementById("order_timer_box").style.display = "none";
					document.getElementById("late_order_label").style.display = "block";
				}
				/*if(time_left_mins < 0){
					stop_timer();
					document.getElementById("order_timer_box").style.display = "none";
					document.getElementById("late_order_label").style.display = "block";
				}*/
			}else{
				document.getElementById("order_timer_box").style.display = "none";
				stop_timer();
				//console.log(ordMaxTime);
			}
		}else{
			document.getElementById("order_timer_box").style.display = "none";
			stop_timer();
		}
	}
	//$scope.ordertime = curr_time.getMinutes() - start_time.getMinutes();
	//$interval(function(){$scope.ordertime = new Date().getMinutes() +3 + ":"+ new Date().getSeconds()},1000);
	
	var interval = $interval(timer, 1000);//Starts the order timer
	
	function stop_timer(){
		$interval.cancel(interval);
	}
	
	/* Generating the options of the Item select tag */
	//getItems($http).then(res => $scope.items =res);
	lineDetails.getItems("../crud/read/getOrdItemsList.php").then(res => $scope.items =res);
	
	/* When user chooses item, apply the rate */
	$scope.changeItem = function(row, index){ lineDetails.applyRate(row, index, editorder_details, $scope.rows);}
	
	/* Computing the Item total from Qty */
	$scope.computeSubTotal = function(/*qty,item*/row, idx){ lineDetails.computeSubTotal(/*qty,item*/row, idx, editorder_details); }
	
	/* Array to hold the number of rows of the orders details */
	$scope.rows = [/*{ID:1}*/];

	/* Function to add a row to the orders details */
	$scope.addRow = /*() => lineDetails.addRow_edit($scope.rows, $scope.showEditDetails, $scope.order_items);*/function(){
		let counter = ($scope.rows.length + 1) + $scope.order_items.length;
		if(!$scope.showEditDetails){
			$scope.showEditDetails = true;
			$scope.rows.push({ID: counter, item: undefined, qty:null, rate:null, total:null, itemSelected: false});
		}else{
			$scope.rows.push({ID: counter, item: undefined, qty:null, rate:null, total:null, itemSelected: false});
		}
	}

	/* Function to reomve a temporary row from the orders details */
	$scope.removeRow = function(index){
		$scope.rows.splice(index, 1);
		editorder_details.splice(index,1);
		if($scope.rows.length === 0){
			$scope.showEditDetails = false;
		}
		console.log($scope.rows);
	}
	
	/* Function to delete a existing record from the orders details */
	$scope.deleteLineItem = function(detailsID, index){
		if($scope.order_items[index].deleted === false){
			$scope.order_items[index].deleted = true;
			deleted_order_lines.push({detailsID, index});
		}else{
			$scope.order_items[index].deleted = false;
			deleted_order_lines.splice(index,1);
		}
		//console.log(deleted_order_lines, $scope.order_items[index]);
	}
	
	//Delete Order Line Promise
	function deletePromise(_data){
		return $http.get("../crud/delete/deleteOrderLine.php", {params: _data});
	}
	
	//Add Order Line Promise
	function addPromise(_data){
		return $http.post("../crud/create/addOrderLine.php", _data);
	}
	
	function collectPromises(){
		let collected = false;
		
		if(deleted_order_lines.length !== 0){
			promisesArr.push(deletePromise({deletedLines: deleted_order_lines}));
			collected = true;
		}
		
		if(editorder_details.length !== 0){
			promisesArr.push(addPromise({ordNo: $routeParams.ordNo, addedLines: editorder_details}));
			collected = true;
		}
		
		return collected;
	}
	
	$scope.validate = function (){ 
		if(collectPromises()){
			//console.log(promisesArr);
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

theApp.controller("order_paymentsCtlr", function($scope, $http, $routeParams){
				
	$scope.ordNo = $routeParams.ordNo;
	$http.post("../crud/read/getOrderPayments.php", {ordNo: $routeParams.ordNo}).then(function(response){/* Generating payments list */
		$scope.bill = Number(response.data.orderbill[0].Bill).toLocaleString();
		$scope.paid = Number(response.data.totalpaid[0].TotalPaid).toLocaleString();
		
		let bal = Number(response.data.orderbill[0].Bill) - Number(response.data.totalpaid[0].TotalPaid);
		$scope.bal = bal.toLocaleString();
		
		if(bal == 0){
			$scope.isFullyPaid = true;
		}
		
		$scope.payments = response.data.ord_paymts;
		//$scope.getGrandTotal = gTotal(response.data.ord_paymts, response.data.ord_paymts.amount);
		console.log(response.data, /*response.data.ord_paymts[0].amount,*/ "Danku");
	}, function(response){
		console.log(response.data);
	});

});

theApp.controller("OrdpaymentCtlr", function($scope, $timeout, $http, $routeParams, httpResponse){
	$scope.ordNo = $routeParams.ordNo;
	
	if($routeParams.pymtID === undefined){
		$scope.pgtitle = "Add";
	}else{
		$scope.pgtitle = "Edit";
		
		/*Generate record for editing */
		let pymtID = $routeParams.pymtID;
		$http.post("../crud/read/getOrderPayment.php", {pymtID: pymtID}).then(function(response){
			if(response.data.status === 1){
				let res = response.data.message[0];
				$scope.ordNo = res.OrderNo;
				$scope.pdate = new Date(res.date)/*.toLocaleDateString("en-GB")*/;
				$scope.pamnt = res.amount;
				$scope.ptype = res.method;
			}
			//console.log(response.data, $scope.pdate);
		}, function(response){
			console.log(response.data);
		});
	}
	
	/* Validate Form Data and submit */
	$scope.validate = function (){
		let pdate = new Date($scope.pdate/*'2022-12-24 04:00:13'*/).toLocaleDateString();
		let form_values = {pdate:pdate, pamnt:$scope.pamnt, ptype:$scope.ptype, ordNo:$scope.ordNo/*, status:$scope.status*/};
		
		if($routeParams.pymtID === undefined){
			$http.post("../crud/create/add_orderpayment.php", form_values).then(function(response){
				httpResponse.success(1, response.data.message);
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			});
			//console.log($scope.pdate, pdate);
		}else{
			let editedfields = {pymtID: $routeParams.pymtID};
			
			//* Use this method as one that works to only choose the edited fields
			angular.forEach($scope.payment_form, function(v, k){
				if(typeof v === 'object' && v.hasOwnProperty('$modelValue') && v.$dirty){
					editedfields[k] = v.$modelValue;
				}
			});
			
			$http.post("../crud/update/setOrderPayment.php", editedfields).then(function(response){
				httpResponse.success(1, response.data.message);
				//console.log(response.data);
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
				//console.log(response.data);
			});
		}
	}
});