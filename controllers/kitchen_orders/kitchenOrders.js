kitchenApp.controller("kitchenOrdersCtlr", function($scope, $http/*, userDetails*/){
	//$scope.ID = userDetails.getUserID();
	
	$http.get("../crud/read/getKitchenOrders.php", {params: {station: sessionStorage.getItem("kit_sta")}})
	.then(function(response){
		$scope.station = sessionStorage.getItem("kit_sta");
		if(response.data.status_orders === 1){
			$scope.orders = response.data.message;/* Generating orders list */
			$scope.showNoItems = false;
			/*$scope.orderStatusFilter = function(status){
				if(response.data.message.status === "Pending" || response.data.message.status === "In Progress"){
					return  response.data.message;
				}
			}*/
			console.log(response.data);
		}
		if(response.data.status_offers == 1){
			$scope.offers = response.data.offers;
			$scope.showNoItems = false;console.log("Yikes");
		}
		
		if(response.data.status_offers != 1 && response.data.status_orders != 1){
			$scope.showNoItems = true;console.log(response.data,$scope.station);
		}
	},function(response){
		console.log(response.data);
	});

});

let ordPreptime = (arr) => {
	let newarr = [];
	for(let v of arr){
		newarr.push(Number(v.PrepTime));
	}
	return Math.max(...newarr);
}

kitchenApp.controller("view_orderCtlr", function($scope, $timeout, $interval, $http, $routeParams, $q/*, $q, userDetails, httpResponse, lineDetails*/){
	//let userID = userDetails.getUserID();
	let odrStartTime;
	let ordMaxTime;
	let ordStatus;
	let isFullyDelivered = true;
	console.log($routeParams.ordNo);
	/*Pre-select icon class for the delete action of order details*/
	//let del_icon = "fa fa-trash";
	//let undo_icon = "fa fa-undo";
	//$scope.iconClass = function(id){ return del_icon;}
	//console.log(icon.length);//.classList.add(del_icon);
	
	$http.post("../crud/read/getOrder.php", {ordNo: $routeParams.ordNo}).then (function(response){
		console.log(response.data);
		$scope.ordNo = $routeParams.ordNo;
		$scope.station = response.data.order[0].Station;
		$scope.to = response.data.order[0].To;
		$scope.delv_point = response.data.order[0].DeliveryPoint;
		odrStartTime = response.data.order[0].OrderDate;
		ordMaxTime = ordPreptime(response.data.ord_details);
		ordStatus = response.data.order[0].OrderStatus;
		angular.forEach(response.data.ord_details, function(v)  {
			v.deleted = false; //Add deleted property for toggling deleted class in ngRepeat
			
			if(Number(v.DeliveredStatus) == 0){
				isFullyDelivered = false;
			}
		})
		$scope.order_items = response.data.ord_details;//edit_ordersPage[0].order_items;
		//$scope.getGrandTotal = gTotal(response.data.ord_details);
		console.log($scope.order_items);
		$scope.showErrorNote = true;
		if(!isFullyDelivered/*ordStatus === "Pending" || ordStatus === "In Progress"*/){
			$scope.showDeliverBtn = true;
			$scope.showTimer = true;console.log(isFullyDelivered);
		}else{
			$scope.showDeliverBtn = false;
			$scope.showTimer = false;console.log(isFullyDelivered);
		}
		
		if(ordStatus === "In Progress"){
			$scope.showRecievedBtn = false;
		}else{
			$scope.showRecievedBtn = true;
		}
	}, function(response){
		console.log(response.data);
	});
	
	$scope.deliverItem = function(curr_status, detailsNo){
		if(curr_status == 0){
			console.log(curr_status, detailsNo,$scope.ordNo);
			$http.post("../crud/update/deliverOrderItem.php",{status: 1, detailsNo: detailsNo, ordNo: $scope.ordNo}).then(function(response){
				if(response.data.status == 1){
					$scope.order_items = response.data.ord_details;
					$timeout(function(){
						$scope.$apply();
						//console.log(response.data.ord_details);
					}, 2000);
				}
			});
		}
	}
	
	$scope.deliver = function(){
		let userName = prompt("Enter your name");
		if(!userName){
			console.log("No name");
			$timeout(function(){
				displayResponseBox(0, "No name entered, try again!");
				
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
				$timeout(resetResponseBox, 6000);
			}, 2000);
			
		}else{
			stop_timer();
			//document.getElementById("order_status").style.display = "none";
			$scope.showTimer = false;
			/*var loader = document.getElementById("loader");
			loader.style.display = "block";*/
			toggleLoader("block");
			let promisesArr = [$http.post("../crud/update/setOrder.php", {orderstatus: "Delivered", ordNo: $routeParams.ordNo/*, userID: userID*/}), $http.post("../crud/update/setOrderStatus.php", {deliveredby: userName, ordNo: $routeParams.ordNo})];
			//$http.post("../crud/update/setOrder.php", {orderstatus: "Delivered", deliveredby: userName, ordNo: $routeParams.ordNo/*, userID: userID*/}).then(function(response){
			$q.all(promisesArr).then(function(response){
				
					$timeout(function(){
						if(response[0].data.status === 1 && response[1].data.status === 1){	
							$scope.showDeliverBtn = false;
							displayResponseBox(1, "Order is marked as Delivered");
						}else{
							displayResponseBox(0, response.data.message);
						}
						//Fadeout response_box after 4sec
						$timeout(fadeout, 4000);
						$timeout(resetResponseBox, 6000);
						exitEditMode("orders_btn");
					}, 2000);
						
				console.log(response.data);
			},function(response){
				console.log(response.data);
			});
		}
	}
	
	
	function timer(){
		
		/*stop_timer();*/
		if(ordStatus === "Pending" || ordStatus === "In Progress"){
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
	
	$scope.$on('$destroy', function() {
	  // Make sure that the interval is destroyed too
	  stop_timer()//$scope.stopFight();
	});
	
	//Marking Order as recieved
	$scope.recieve = function(){
		let userName = prompt("Enter your name");
		if(!userName){
			console.log("No name");
			$timeout(function(){
				displayResponseBox(0, "No name entered, try again!");
				
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
				$timeout(resetResponseBox, 6000);
			}, 2000);
			
		}else{
			console.log(userName);
			toggleLoader("block");
			let promisesArr = [$http.post("../crud/update/setOrder.php", {orderstatus: "In Progress", ordNo: $routeParams.ordNo/*, userID: userID*/}), $http.post("../crud/update/setOrderStatus.php", {recievedby: userName, ordNo: $routeParams.ordNo})];
			//$http.post("../crud/update/setOrder.php", {orderstatus: "In Progress",recievedby: userName, ordNo: $routeParams.ordNo/*, userID: userID*/}).then(function(response){
			$q.all(promisesArr).then(function(response){
				$timeout(function(){
					if(response[0].data.status === 1 && response[1].data.status === 1){	
						$scope.showRecievedBtn = false;
						displayResponseBox(1, "Order is marked as Recieved");
					}else{
						displayResponseBox(0, response.data.message);
					}
					//Fadeout response_box after 4sec
					$timeout(fadeout, 4000);
					$timeout(resetResponseBox, 6000);
					//let notification = new Notification("Nofication", {body: "Here it is", silent: false});
					//exitEditMode("orders_btn");
				}, 2000);
				
			console.log(response[1].data);
		},function(response){
			console.log(response.data);
		});
			/*$timeout(function(){
				displayResponseBox(1, "Order is marked as Recieved");
				
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
				$timeout(resetResponseBox, 6000);
			}, 2000);*/
		
		}
	}
	

});

kitchenApp.controller("view_offerCtlr", function($scope, $http, $routeParams,$timeout/*, userDetails, lineDetails, httpResponse*/){

		if($routeParams.category === "Offers"){
			$scope.category = "Offers";
			$scope.show = true;
		}else if($routeParams.category === "Items Spoilt"){
			$scope.category = "Items Spoilt";
		}else{
			$scope.category = "Missing Items";
		}
		//console.log(getEditUrl());
		$http.get(getEditUrl()).then(function(response){console.log(response.data);
			$scope.station = response.data.extra[0].Station;
			$scope.to = response.data.extra[0].RecipientCategory;
			$scope.extras = response.data.extra_details;
			$scope.showDeliverBtn = true;
		},function(response){
			console.log(response);
		});
		
		function getEditUrl(){
			if($routeParams.category === "Offers"){
				return "../crud/read/getOffer.php/?offerID="+ $routeParams.ID;
			}else if($routeParams.category === "Items Spoilt"){
				return "../crud/read/getSpoilt.php/?spoiltID="+ $routeParams.ID;
			}else{
				return "../crud/read/getMissing.php/?missingID="+ $routeParams.ID;
			}
		}
	
	$scope.deliver = function(){
		let userName = prompt("Enter your name");
		if(!userName){
			console.log("No name");
			$timeout(function(){
				displayResponseBox(0, "No name entered, try again!");
				
				//Fadeout response_box after 4sec
				$timeout(fadeout, 4000);
				$timeout(resetResponseBox, 6000);
			}, 2000);
			
		}else{
			toggleLoader("block");
			//let promisesArr = [$http.post("../crud/update/setOrder.php", {orderstatus: "Delivered", ordNo: $routeParams.ordNo/*, userID: userID*/}), $http.post("../crud/update/setOrderStatus.php", {deliveredby: userName, ordNo: $routeParams.ordNo})];
			//$http.post("../crud/update/setOrder.php", {orderstatus: "Delivered", deliveredby: userName, ordNo: $routeParams.ordNo/*, userID: userID*/}).then(function(response){
			$http.post("../crud/update/setOffer.php", {offerID: $routeParams.ID, isDelivered: 1}).then(function(response){
				
					$timeout(function(){
						if(response.data.status === 1){	
							$scope.showDeliverBtn = false;
							displayResponseBox(1, "Order is marked as Delivered");
						}else{
							displayResponseBox(0, response.data.message);
						}
						//Fadeout response_box after 4sec
						$timeout(fadeout, 4000);
						$timeout(resetResponseBox, 6000);
						exitEditMode("offers_btn");
					}, 2000);
						
				console.log(response.data);
			},function(response){
				console.log(response.data);
			});
		}
	}
	
});

