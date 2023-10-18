theApp.controller("search_filterCtlr", function($scope, $http, $timeout, lineDetails, userDetails){
	
	if(userDetails.getUserLevel() === "Level1"){
		$scope.station = userDetails.getStation();//Pre-select station based on current station
		$scope.showSrchCat = false;//wether to show search catgories
	}else{
		$scope.showSrchCat = true;
	}
	
	$scope.changeStation = function(){
		if($scope.station == undefined /*|| $scope.rep_cat == ""*/){
			$scope.date_sec = false;
			$scope.srch_cat = undefined;
			$scope.orders_sec = false;
			$scope.reqs_sec = false;
			$scope.offers_sec = false;
			$scope.showItem = false;
		}
	};
	
	$scope.changeSearchCategory = function(){
		if($scope.station == undefined || $scope.srch_cat == undefined){
			$scope.date_sec = false;
			$scope.orders_sec = false;
			$scope.reqs_sec = false;
			$scope.offers_sec = false;
			$scope.showItem = false;
		}else if($scope.srch_cat == "orders"){
			$scope.date_sec = true;
			$scope.orders_sec = true;
			$scope.showItem = true;
			$scope.reqs_sec = false;
			$scope.offers_sec = false;
		}else if($scope.srch_cat == "requisitions"){
			$scope.date_sec = true;
			$scope.reqs_sec = true;
			$scope.showItem = true;
			$scope.orders_sec = false;
			$scope.offers_sec = false;
		}else if($scope.srch_cat == "offers"){
			$scope.date_sec = true;
			$scope.offers_sec = true;
			$scope.showItem = true;
			$scope.orders_sec = false;
			$scope.reqs_sec = false;
		}
		
	};
	
	$scope.changeDateFilter = function(){
		if($scope.date_filter == "_date"){
			$scope._date = true;
			$scope.date_range = false;
		}else if($scope.date_filter == "range"){
			$scope._date = false;
			$scope.date_range = true;		
		}else{
			$scope._date = false;
			$scope.date_range = false;
		}
	};
	
	$scope.checkToDate = function(){
		if($scope.fro_date < $scope.to_date){
				alert("From Date cannot be after the To Date");console.log($scope.fro_date,$scope.to_date, $scope.fro_date<$scope.to_date);
				$scope.to_date = undefined;
		}
	}
	
	$scope.checkFromDate = function(){
		if($scope.fro_date > $scope.to_date){
				alert("To Date cannot be before the From Date");//console.log($scope.fro_date,$scope.to_date, $scope.fro_date<$scope.to_date);
				$scope.to_date = undefined;
		}
	}
	
	$scope.changeOrdersCategory = function(){
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
	};
	
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
				{pntName: "Annex"},
				{pntName: "Barca"},
				{pntName: "Braxton"},
				{pntName: "Cindy"},
				{pntName: "Crown"},
				{pntName: "Cubic"},
				{pntName: "Elbrus"},
				{pntName: "Everest"},
				{pntName: "Finex"},
				{pntName: "Houston"},
				{pntName: "Jojo"},
				{pntName: "Kintar"},
				{pntName: "Leads"},
				{pntName: "Marie"},
				{pntName: "Miami"},
				{pntName: "Middle"},
				{pntName: "Mishel"},
				{pntName: "Newton"},
				{pntName: "Nic"},
				{pntName: "Parkers"}, 
				{pntName: "Saints"},
				{pntName: "Tasha"},
				{pntName: "Trend"},
				{pntName: "Tidy"},
				{pntName: "Tides"},
				{pntName: "Top"},
				{pntName: "Tournest"},
				{pntName: "Vegas"}
			];
		return room;
	}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getReqItemsList.php").then(res => $scope.items = res);
	
	
	$scope.validate = function(){
		
		function checkForm(){
			let formComplete = true;
			
			
			return formComplete;
		}
		
		if(checkForm){
			let formdata = new FormData(document.getElementsByName("search_filter_form")[0])
			
			//ordDelvPnt form name attaches 'string:' to delvpnt
			//Used following snippet to set the name
			//look into problem later
			for(let [name, value] of formdata){
				if(name == 'ordDelvPnt'){
					formdata.set(name, $scope.delv_point);
				}
				
				if(name == 'item_id' && value != ''){console.log(name, value,$scope.item_id);
					//if($scope.item_id.value != undefined){
						formdata.set(name, $scope.item_id.value);
					//}
				}
			}
			let form_values = Object.fromEntries(formdata);console.log(form_values);
			
			sessionStorage.setItem("search_filter", JSON.stringify(form_values));
			console.log(form_values);
			$scope.hideForm = true;
			toggleLoader("block");
			$timeout(function(){
				toggleLoader("none");
				document.getElementById("srch_res_link").click();
			}, 1500);
		
		}
	};
	
});


theApp.controller("search_resultCtlr", function($scope, $http, $timeout,$compile){
	let search_items = JSON.parse(sessionStorage.getItem("search_filter"));
	let station = search_items.station;
	let category = search_items.srchcategory;
	console.log(search_items);
	toggleLoader("none");
	//$scope.srch_res_desc = 
	
	$http.get(getUrl(category), {params: {_data: search_items}}).then(function(response){
		console.log(response.data);
		createEl();
		if(response.data.status == 1){
			toggleLoader("none")
			
			$scope.listItems = response.data.message;
			$scope.showNoItems = false;
			
		}else if(response.data.status == 2){
			$scope.showNoItems = true;
			toggleLoader("none")
			console.log(response.data.message);
		}else{
			alert(response.data.message);
		}
		/*if(category == "orders"){
			
		}else if(category == "requisitions"){
			if(response.data.status == 1){
				toggleLoader("none")
				
				$scope.listItems = response.data.message;
				$scope.showNoItems = false;
				
			}else if(response.data.status == 2){
				$scope.showNoItems = true;
				toggleLoader("none")
				console.log(response.data.message);
			}else{
				alert(response.data.message);
			}
			
		}else if(category == "offers"){
		
		}*/
	}, function(response){
		console.log(response.data);
	});
	
	function getUrl(cat){
		let url;
		
		if(cat == "orders"){
			url = "../crud/read/searchOrders.php";
		}else if(cat == "requisitions"){
			url = "../crud/read/searchRequisitions.php";
		}else if(cat == "offers"){
			url = "../crud/read/searchOffers.php";
		}
		
		return url;
	}
	
	function createEl(){
		let gridView = document.createElement('div');
		gridView.setAttribute('id', 'grid_view');
		
		let gridViewHeader = document.createElement('div');
		gridViewHeader.setAttribute('id', 'grid_view_header');
		
		let detailsContent = document.createElement('div');
		detailsContent.setAttribute('id', 'details_content_box');
		
		let detailsContent_w_maxheight = document.createElement('div');
		detailsContent_w_maxheight.style.maxHeight = '200px';
		detailsContent_w_maxheight.style.overflowY = 'auto';
		
		let gridViewContent = document.createElement('div');
		gridViewContent.classList.add('grid_view_content');
		gridViewContent.dataset.ngRepeat = "listItem in listItems track by $index"
		gridViewContent.classList.add("animate_repeat");
		
		let noItemsBox = document.createElement('div');
		noItemsBox.setAttribute('id', 'no_items_container');
		noItemsBox.classList.add('grid_view_content', 'animate_show');
		noItemsBox.dataset.ngShow = "showNoItems";
		
		let noItemsSpan = document.createElement('span');
		noItemsSpan.style.color = 'red';
		
		let txt = document.createTextNode('There are no records matching your search ');
		noItemsSpan.appendChild(txt);
		
		let noItemsSpanIcon = document.createElement('i');
		noItemsSpanIcon.classList.add('fa', 'fa-times-circle');
		
		gridView.appendChild(createListHeader(category, getListCols(category), gridViewHeader));
		detailsContent_w_maxheight.appendChild(createListContent(category, getListCols(category), gridViewContent));
		detailsContent.appendChild(detailsContent_w_maxheight);
		
		noItemsSpan.appendChild(noItemsSpanIcon);
		noItemsBox.appendChild(noItemsSpan);
		detailsContent.appendChild(noItemsBox);
		
		gridView.appendChild(detailsContent);
		
		//Dynamically added component should be compiled using angular, use $compile function 
		$compile(detailsContent)($scope);
		
		gridView.appendChild(detailsContent);
		document.querySelector('#list_content_box').appendChild(gridView);
		
	}
	
	function createListHeader(cat, listColsArr, listHeader){
		/*if(cat == 'orders'){
		
		}else if(cat == 'requisitions'){
			for let
		}else if(cat == 'offers'){
		
		}*/
		for(let listCol in listColsArr){
			let headerCell = document.createElement('div');
			headerCell.classList.add('grid_view_header_cell');
			let cellSpan = document.createElement('span');
			let txt = document.createTextNode(listColsArr[listCol]);
			cellSpan.appendChild(txt);
			headerCell.appendChild(cellSpan);
			
			listHeader.appendChild(headerCell);
		}
		return listHeader;
	}
	
	function createListContent(cat, listColsArr, listContent){
		let icon = document.createElement('i');
		icon.classList.add('fa', 'fa-eye');
		let link = document.createElement('a');
		link.setAttribute('href', viewLinkAttribute(cat));
		link.appendChild(icon);
		
		for(let listCol in listColsArr){
			let contentCell = document.createElement('div');
			contentCell.classList.add('grid_view_content_cell');
			let cellSpan = document.createElement('span');
			let txt; 
			if(listColsArr[listCol] == '#'){
				txt	= document.createTextNode('{{$index+1}}');
			}else if(listColsArr[listCol] == 'View'){
				txt = link;//document.createTextNode('link');
			}else{
				txt = document.createTextNode(`{{listItem.${listCol}}}`);
			}
			cellSpan.appendChild(txt);
			contentCell.appendChild(cellSpan);
			
			listContent.appendChild(contentCell);
		}
		return listContent;
	}
	
	function viewLinkAttribute(cat){
		let lnkAttr;
		if(cat == 'orders'){
			lnkAttr = '#!view_order/{{listItem.ordNo}}';
		}else if(cat == 'requisitions'){
			lnkAttr = '#!view_requisition/{{listItem.reqNo}}/{{listItem.type}}';
		}else if(cat == 'offers'){
			lnkAttr = '#!view_offer/Offers/{{listItem.ID}}';
		}
		
		return lnkAttr;
	}
	
	function getListCols(cat){
		let listCols;
		if(cat == 'orders'){
			listCols = ordersListCols();
		}else if(cat == 'requisitions'){
			listCols = reqsListCols();
		}else if(cat == 'offers'){
			listCols = offersListCols();
		}
		
		return listCols;
	}
	
	function ordersListCols(){
		let listCols;
		listCols = {noCol: '#', station: 'Station', to: 'To', time: 'Time', status: 'Status', bill: 'Bill', payment: 'Payment', view: 'View'}
		
		return listCols;
	}
	
	function reqsListCols(){
		let listCols;
		listCols = {noCol: '#', _date: 'Time', category: 'For', station: 'Station', status: 'Status', staff: 'Staff', amount: 'Amount', view: 'View'}
		
		return listCols;
	}
	
	function offersListCols(){
		let listCols;
		listCols = {noCol: '#', _date: 'Date', RecipientCategory: 'For', station: 'Station', staff: 'Staff', view: 'View'}
		
		return listCols;
	}

});
