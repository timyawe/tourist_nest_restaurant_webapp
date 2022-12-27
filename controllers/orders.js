theApp.controller("ordersCtlr", function($scope, $http, userDetails){
	$scope.ID = userDetails.getUserID();
	$http.get("../crud/read/getOrders.php", {params: {station: userDetails.getStation()}}).then(function(response){
		$scope.orders = response.data;/* Generating orders list */
		console.log(response.data);
	},function(response){
		console.log(response.data);
	});

});

theApp.controller("create_orderCtlr", function($scope, $timeout, $http, userDetails){

	$scope.station = userDetails.getStation();
	$scope.showEditBtn = false;
	/* When user chooses delivery point */
	$scope.changeTo = function(){
		if($scope.to === "Table" /*|| $scope.to === "Room"*/){
			$scope.show = true;
			$scope.delvPnts = tables();
		}else if($scope.to === "Room"){
			$scope.show = true;
			$scope.delvPnts = rooms();
		}else{
			$scope.show = false;
			$scope.delvPnts = undefined;
		}
	}

	let order_details = [];//to contain order details for form submission

	/* Generate Requisition details Grand Total */
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
	$scope.itemRate = function(x, idx){
		//console.log(x, idx);
		let qty_input = document.getElementsByClassName("qty_input");
		let rate_input = document.getElementsByClassName("rate_input");
		for(let i = 0; i<rate_input.length; i++){
			if(idx === i){
				rate_input[i].value = x.rate;
				
				order_details[idx] = {pdtNo:x.value/*, item:x.label*/};//adding the order details' objects to array by the current index
				
				//Apply the total if the qty field is already filled
				if(qty_input[i].value !== undefined){
					let qty = qty_input[i].value;
					$scope.computeSubTotal(qty,x,idx);
				}
			}
		}
		
	}


	/* Computing the Item total from Qty */
	$scope.computeSubTotal = function(qty,item, idx){
		let total_input = document.getElementsByClassName("total_input");
		if(item !== undefined && qty !== undefined ){
			for(let x = 0; x < total_input.length; x++){
				if(idx === x){
					total_input[x].value = qty * item.rate;
					
					//adding the requisition details' objects' properties by the current index
					order_details[idx].qty = qty;
					order_details[idx].rate = item.rate;
					order_details[idx].subtotal = qty * item.rate;
				}
			}
		}
	}

	$scope.q = function(){
		console.log($scope.delv_point)
	}
	
	/* Generating the options of the Delivery points select tag */
	$http.get("../crud/read/getTableDelvPoints.php").then(function(response){
		//tables = () => response.data.message;//Arrow function declaration
		console.log(tables(), "Danku");
	},function(response){
		console.log(response.data);
	});
	
	
	function tables(){
		table = [
			
				{pntName: "Table 1"}, 
				{pntName: "Table 2"},
				{pntName: "Lower Western"}
				];
		return table;
	}

	function rooms(){
		room = [
			
				{pntName: "Braxton"}, 
				{pntName: "Parkers"}, 
				{pntName: "Nic"}
			];
		return room;
	}
	/*$scope.delvPnts = [
		{
			table: [
			
				{pntName: "Table 1"}, 
				{pntName: "Table 2"},
				{pntName: "Lower Western"}
				],
				
			room: [
			
				{pntName: "Braxton"}, 
				{pntName: "Parkers"}, 
				{pntName: "Nic"}
			]
		}

	]*/

	/* Generating the options of the Item select tag */
	$http.get("../crud/read/getOrdItemsList.php").then(function(response){
		$scope.items = response.data;
	});

	/* Array to hold the number of rows of the orders details */
	$scope.rows = [{ID:1}];

	/* Function to add a row to the orders details */
	$scope.addRow = function(){
		let counter = $scope.rows.length + 1;
		$scope.rows.push({ID: counter});
	}

	/* Function to reomve a row from the orders details */
	$scope.removeRow = function(index){
		if($scope.rows.length === 1){
			alert("Order must have atleast one item");
		}else{
			$scope.rows.splice(index, 1);
		}
	}

	/* Validate Form Data and submit */
	$scope.validate = function (){

		let form_values = {station:$scope.station, to:$scope.to, delv_point:$scope.delv_point, details:order_details, userID: userDetails.getUserID()};
		//console.log(form_values);
		//for(let prop in form_values){console.log(form_values[prop])};
		//for(let z=0; z<req_details.length; z++){console.log(req_details[z])}
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

let ordPreptime = (arr) => {
	let newarr = [];
	for(let v of arr){
		newarr.push(Number(v.PrepTime));
	}
	return Math.max(...newarr);
}

theApp.controller("edit_orderCtlr", function($scope, $timeout, $interval, $http, $routeParams, userDetails){
	let odrStartTime;
	let ordMaxTime;
	let ordStatus;
	
	if(userDetails.getUserLevel() === "Level2"){
		$scope.showVerify = true;
	}
	
	$http.post("../crud/read/getOrder.php", {ordNo: $routeParams.ordNo}).then (function(response){
		/*var edit_ordersPage = response.data;*/
		$scope.ordNo = $routeParams.ordNo;
		$scope.station = response.data.order[0].Station;
		$scope.to = response.data.order[0].To;
		$scope.delv_point = response.data.order[0].DeliveryPoint;
		odrStartTime = response.data.order[0].OrderDate;
		ordMaxTime = ordPreptime(response.data.ord_details);
		ordStatus = response.data.order[0].OrderStatus;
		$scope.order_items = response.data.ord_details;//edit_ordersPage[0].order_items;
		$scope.getGrandTotal = gTotal(response.data.ord_details);
		
		if(ordStatus === "Pending"){
			$scope.showDeliverBtn = true;
		}
	}, function(response){
		console.log(response.data);
	});
	
	/*var edit_ordersPage = [{"station": "Restaurant",
							"to": "Table",
							"delv_point": "Upper Western",
							"order_items": [
				{number: 1,item: "Chicken",qty: 1,rate: "7,000",total: "7,000"},
				{number: 2,item: "Chips",qty: 1,rate: "7,000",total: "7,000"}
			]
		}					
	]
	$scope.station = edit_ordersPage[0].station;
	$scope.to = edit_ordersPage[0].to;
	$scope.delv_point = edit_ordersPage[0].delv_point;
	$scope.order_items = edit_ordersPage[0].order_items;*/

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
			if(ordMaxTime !== 0){
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
	}//console.log($scope.ordertime);
	//$scope.ordertime = curr_time.getMinutes() - start_time.getMinutes();
	//$interval(function(){$scope.ordertime = new Date().getMinutes() +3 + ":"+ new Date().getSeconds()},1000);
	
	var interval = $interval(timer, 1000);//Starts the order timer
	
	function stop_timer(){
		$interval.cancel(interval);
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
	
	/*var payments_page = [{"bill": "14,000",
						  "payments_list": [
				{pymtID: 1,amount: "10,000",type: "Mobile Money", date: "12/11/2022 10:00 AM"},
				{pymtID: 2,amount: "4,000",type: "Cash", date: "12/11/2022 11:00 AM"}
			]
		}
	]*/
	
	
	//$scope.bill = payments_page[0].bill;
	//$scope.payments = payments_page[0].payments_list;

});

theApp.controller("OrdpaymentCtlr", function($scope, $timeout, $http, $routeParams){
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
		
		//for(let prop in form_values){console.log(form_values[prop])};
		if($routeParams.pymtID === undefined){
			$http.post("../crud/create/add_orderpayment.php", form_values).then(function(response){
				
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
				toggleLoader("block");
				
				$timeout(function(){
					if(response.data.status === 1){
						displayResponseBox(1, response.data.message);
					}else if(response.data.status === 0){
						displayResponseBox(0, response.data.message);
					}else{
						displayResponseBox(2, response.data.message);
					}
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				//console.log(response.data);
			}, function(response){
				toggleLoader("block");
				
				$timeout(function(){
					displayResponseBox(0);
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
				}, 2000);
				//document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
				console.log(response.data);
			});
		}
	}
});