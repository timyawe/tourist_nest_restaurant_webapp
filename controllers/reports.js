theApp.controller("report_filterCtlr", function($scope, $timeout, lineDetails, userDetails){
	//let formComplete = false;
	
	if(userDetails.getUserLevel() === "Level1"){
		$scope.station = userDetails.getStation();//Pre-select station based on current station
	}
	
	$scope.changeStation = function(){
		if($scope.station == "" /*|| $scope.rep_cat == ""*/){
			$scope.showType = false;
			$scope.rep_cat = undefined;
		}/*else{
			$scope.showType = true;
		}*/
		//if($scope.station !== undefined /*&& $scope.rep_cat !== undefined || $scope.station !== "" /*&& $scope.rep_cat !== ""*/){
		/*	$scope.showType = true;
		}else if ($scope.station == ""){
			$scope.showType = false;
		}else{
			$scope.showType = false;
		}*/
	};
	
	$scope.changeCategory = function(){
		if($scope.station == "" || $scope.rep_cat == ""){
			$scope.showType = false;
			$scope.showFields = false;
		}else if($scope.rep_cat == "General"){
			$scope.showType = true;
			$scope.showFields = false;console.log($scope.rep_type);
		}else if($scope.rep_cat == "individual"){
			$scope.showType = true;
			$scope.showFields = true;
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
	
	/*let checks = document.querySelectorAll('.check');
	for(let check of checks){
		check.addEventListener('click', isClicked);
	}
	
	function isClicked(){
		for(let check of checks){
			if(check.checked){alert(checks.length);
				$scope.showType = true;
			}
		}
	}*/	
	
	$scope.clickPeriod = function(e){
		e.preventDefault();
		alert('This option is auto selected and only changes when you select or unselect the "By Date" option');
	}
	
	$scope.changeDateFilter = function(){
		if(document.querySelector('#date').checked){
			document.querySelector('#period').checked = false;console.log(document.querySelector('#period').value);
			$scope.showDate = true;//formComplete = true;console.log(formComplete);
		}else{
			document.querySelector('#period').checked = true;console.log(document.querySelector('#period').value);
			$scope.showDate = false;
			$scope.fro_date = undefined;
			$scope.to_date = undefined;
		}
	}
	
	$scope.changeItemCatFilter = function(){
		if(document.querySelector('#item_cat').checked){
			if(document.querySelector('#item_name').checked){
				document.querySelector('#item_name').click(); //console.log("Click");// = false;
			}
			$scope.showItemCategory = true;//console.log("Click");
			$scope.showItem = false;
			$scope.item = undefined;
		}else{
			$scope.showItemCategory = false;//console.log("Click");
			$scope.item_cat = undefined;
		}
		//console.log("Changed");
	}
	
	/* Generating the options of the Item select tag */
	lineDetails.getItems("../crud/read/getOrdItemsList.php").then(res => $scope.items =res);
	
	$scope.changeItemFilter = function(){
		if(document.querySelector('#item_name').checked){
			if(document.querySelector('#item_cat').checked){
				document.querySelector('#item_cat').click()// = false;
			}
			$scope.showItem = true;
			$scope.showItemCategory = false;
			$scope.item_cat = undefined;
		}else{
			$scope.showItem = false;
			$scope.item = undefined;
		}
	}
	
	$scope.validate = function(){
		
		function checkForm(){
			let formComplete = true;
			
			/*if(document.querySelector('#period').checked){
				formComplete = true;
			}*/if(document.querySelector('#date').checked && !angular.isDefined($scope.fro_date) && !angular.isDefined($scope.to_date)){
				formComplete = false;
				//console.log("Defined",$scope.fro_date,$scope.to_date,$scope.item,formComplete);
			}
			
			if(document.querySelector('#date').checked && !angular.isDefined($scope.from_period) && !angular.isDefined($scope.to_period)){
				formComplete = false;
				//console.log("Defined",$scope.fro_date,$scope.to_date,$scope.item,formComplete);
			}
			
			if(document.querySelector('#item_name').checked && !angular.isDefined($scope.item_name)){
				formComplete = false;
			}
			
			if(document.querySelector('#item_cat').checked && !angular.isDefined($scope.item_cat)){
				formComplete = false;
			}
			
			if($scope.rep_cat == "individual" && form_values.rep_cols.length == 0){
				formComplete = false;
			}
			return formComplete;
		}
		
		let formdata = new FormData(document.getElementsByName("report_filter_form")[0])
		let form_values = Object.fromEntries(formdata);//console.log(form_values);
		
		if($scope.rep_cat == "individual"){
			form_values.rep_cols = formdata.getAll('rep_cols');
		}
		
		if(document.querySelectorAll('.filter_type').length > 1/* && document.querySelector('#item').checked*/){
			form_values.filter_type = formdata.getAll('filter_type');
		}
		
		if(checkForm()){
			if(form_values.rep_cols.length == 4){
				form_values.rep_cols.push("missing");
			}
			sessionStorage.setItem("report_filter", JSON.stringify(form_values));
			console.log(form_values);
			$scope.hideForm = true;
			toggleLoader("block");
			$timeout(function(){
				toggleLoader("none");
				document.getElementById("rep_res_link").click();
			}, 1500);
		}else{
			alert("The options chosen are not enough to generate a report. Choose relevant options and try again");
			console.log(form_values, $scope.item_name, $scope.item_cat);
			console.log(document.querySelector('#item_name').checked, document.querySelector('#item_cat').checked);
		}
	}
});

theApp.controller("report_resultCtlr", function($scope, $http, $compile){
	let filter_items = JSON.parse(sessionStorage.getItem("report_filter"));
	let station = filter_items.station;
	let category = filter_items.category;
	let type = filter_items.filter_type;
	/*if(category === "individual"){*/rep_cols = filter_items.rep_cols//}
	let table_cols;
	if(rep_cols.length == 5){
		table_cols = {no_col:"#", item: "Item", Start: "Start"};
	}else{
		table_cols = {no_col:"#", item: "Item"};
	}
		
	if(rep_cols !== undefined){
		rep_cols.forEach((el, idx) =>{
			table_cols[el] = el;//console.log(idx, el);
		});
		//rep_cols.map((el) => {table_cols[el] = el;console.log( el);})
	}
	
	/*if(rep_cols.length == 4){
		table_cols['Missing'] = 'Missing';
	}*/
	
	if(rep_cols.length == 5){
		table_cols['Finish'] = 'Finish';
	}
	
	function createEl(){
		let table = document.createElement('table');
		//table.className = '_table';
		let thead = document.createElement('thead');
		//thead.classList.add('_thead');
		let head_row = document.createElement('tr');
		for(let table_col in table_cols){
			let th = document.createElement('th');
			let txt = document.createTextNode(table_cols[table_col]);
			th.appendChild(txt);
			head_row.appendChild(th);
		}
		
		thead.appendChild(head_row);
		table.appendChild(thead);
		
		let tbody = document.createElement('tbody');
		let body_row = document.createElement('tr');
		body_row.dataset.ngRepeat = "table_row in table_rows track by $index";//Adding data related attributes
		body_row.classList.add("animate_repeat"/*, "ng-scope"*/);
		for(let table_col in table_cols){
			let td = document.createElement('td');
			let txt;
			if(table_cols[table_col] == "#"){
				txt = document.createTextNode('{{$index+1}}');
			}else{
				 txt = document.createTextNode(`{{table_row.${table_col}}}`);
			}
			if(table_cols[table_col] !== "#" && table_cols[table_col] !== "Item"){
				td.classList.add('qty_col');
			}
			if(table_cols[table_col] == "Start"){
				td.style.fontWeight = 'bold';
			}
			if(table_cols[table_col] == "missing"){
				td.style.color = 'red';

			}
			if(table_cols[table_col] == "Finish"){
				td.style.fontWeight = 'bold';
			}
			td.appendChild(txt);
			body_row.appendChild(td);
		}
		tbody.appendChild(body_row);
		//Dynamically added component should be compiled using angular, use $compile function 
		$compile(tbody)($scope);
		table.appendChild(tbody);
		document.querySelector('#table_content_box').appendChild(table);
	}
	$http.get("../crud/read/getReport.php", {params: {_data: filter_items}}).then(function(response){
		console.log(response.data);
		createEl();
		$scope.table_rows = response.data.message;
		toggleLoader("none");
	},function(response){
		//createEl();
	});
	
	let currMonth = new Date().toLocaleString('default', {month: 'long'});
	let from_date = new Date(filter_items.from_date).toLocaleDateString();
	let to_date = new Date(filter_items.to_date).toLocaleDateString();
	let from_period, to_period;
	
	if(filter_items.from_period){
		if(filter_items.from_period == "19:00:00"){
			from_period = "Night";
		}else{
			from_period = "Day";
		}
	}
	
	if(filter_items.to_period){
		if(filter_items.to_period == "19:00:00"){
			to_period = "Night";
		}else{
			to_period = "Day";
		}
	}
	
	function filterItemType(){
		let itemtype;
		if(type.includes('item_cat')){
			itemtype = "Category";
		}else{
			itemtype = "Name";
		}
		return itemtype;
	}
	
	if(Array.isArray(type)){
		if(type.length === 1){
			if(type.includes('period')){
				$scope.report_desc = `${station} Report For ${currMonth}`;
			}else{
				$scope.report_desc = `${station} Report From ${from_date} ${from_period} To ${to_date} ${to_period}`;
			}
		}else{
			if(type.includes('period')){
				if(filterItemType() == "Category"){
					$scope.report_desc = `${station} ${filter_items.item_cat} Report For ${currMonth}`;
				}else{
					$scope.report_desc = `${station} Report For ${currMonth} By Item`;
				}
			}else{
				if(filterItemType() == "Category"){
					$scope.report_desc = `${station} ${filter_items.item_cat} Report From ${from_date} ${from_period} To ${to_date} ${to_period}`;
				}else{
					$scope.report_desc = `${station} Report From ${from_date} ${from_period} To ${to_date} ${to_period} By Item`;
				}
			}
		}
	}
	
	//$scope.table_rows = [{item: "Mirinda Pineapple", qty_sold: 25}, {item: "Club", qty_sold: 125}]
});
