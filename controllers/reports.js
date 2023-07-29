theApp.controller("report_filterCtlr", function($scope, $timeout, lineDetails, userDetails){
	//let formComplete = false;
	
	if(userDetails.getUserLevel() === "Level1"){
		$scope.station = userDetails.getStation();//Pre-select station based on current station
		$scope.showRepCat = false;//wether to show report catgeries
	}else{
		$scope.showRepCat = true;
	}
	
	$scope.changeStation = function(){
		if($scope.station == "" /*|| $scope.rep_cat == ""*/){
			$scope.showType = false;
			$scope.rep_cat = undefined;
			$scope.showItemQtyFilter_sec = false;
			$scope.showItemCategory = false;
			//$scope.showFields_amounts = false;
			$scope.showItemAmountsDate = false;
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
			$scope.showItemQtyFilter_sec = false;
			$scope.showItemCategory = false;
			//$scope.showFields_amounts = false;
			$scope.showItemAmountsDate = false;
			$scope.showItemAcc_sec = false;
		}else if($scope.rep_cat == "General"){
			$scope.showType = true;
			$scope.showFields = false;console.log($scope.rep_type);
		}else if($scope.rep_cat == "quantities"){
			$scope.showType = true;
			$scope.showFields = true;
			$scope.showItemQtyFilter_sec = true;
			$scope.showItemCategory = false;
			//$scope.showFields_amounts = false;
			$scope.showItemAmountsDate = false;
			$scope.showItemAcc_sec = false;
		}else if($scope.rep_cat == "amounts"){
			$scope.showItemQtyFilter_sec = false;
			$scope.showItemCategory = true;
			$scope.showItemAmountsDate = true;
			//$scope.showFields_amounts = true;
			$scope.showType = false;
			$scope.showFields = false;
			$scope.showItemAcc_sec = false;
		}else if($scope.rep_cat == "accountabilities"){
			$scope.showItemCategory = true;
			$scope.showItemAcc_sec = true;
			$scope.showFields = false;
			$scope.showType = false;
			$scope.showItemAmountsDate = false;
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
	
	$scope.changeAccDateFilter = function(){
		if($scope.item_acc_date_filter == "monthly"){
			$scope.showAccMonthlyFilter = true;
			$scope.showAccDateRangeFilter = false;
		}else if($scope.item_acc_date_filter == "range"){
			$scope.showAccDateRangeFilter = true;
			$scope.showAccMonthlyFilter = false;
		}else{
			$scope.showAccMonthlyFilter = false;
			$scope.showAccDateRangeFilter = false;
		}
	}
	
	$scope.validate = function(){
		
		function checkForm(){
			let formComplete = true;
			
			if($scope.rep_cat == "quantities"){
				/*if(document.querySelector('#period').checked){
					formComplete = true;
				}*/if(document.querySelector('#date').checked && !angular.isDefined($scope.fro_date) && !angular.isDefined($scope.to_date)){
					formComplete = false;
					//console.log("Defined",$scope.fro_date,$scope.to_date,$scope.item,formComplete);
				}
				
				/*if(document.querySelector('#date').checked && !angular.isDefined($scope.from_period) && !angular.isDefined($scope.to_period)){
					formComplete = false;
					//console.log("Defined",$scope.fro_date,$scope.to_date,$scope.item,formComplete);
				}*/
				
				if(document.querySelector('#item_name').checked && !angular.isDefined($scope.item_name)){
					formComplete = false;
				}
				
				if(document.querySelector('#item_cat').checked && !angular.isDefined($scope.item_cat)){
					formComplete = false;
				}
				
				if($scope.rep_cat == "quantities" && form_values.rep_cols.length == 0){
					formComplete = false;
				}
			}else if($scope.rep_cat == "amounts"){
				if(!angular.isDefined($scope.amounts_date)){
					formComplete = false;
				}
				
				/*if($scope.rep_cat == "amounts" && form_values.rep_cols.length == 0){
					formComplete = false;
				}*/
			}if($scope.rep_cat == "accountabilities"){
				if(!angular.isDefined($scope.item_cat)){
					formComplete = false;
				}
								
				if(!angular.isDefined($scope.item_acc_date_filter)){
					formComplete = false;
				}else{
					if($scope.item_acc_date_filter == "monthly"){
						if(!angular.isDefined($scope.item_acc_date_filter_year) || !angular.isDefined($scope.item_acc_date_filter_month)){
							formComplete = false;
						}
					}else{
						if(!angular.isDefined($scope.fro_date) || !angular.isDefined($scope.to_date)){
							formComplete = false;
						}
					}
				}
			}
			return formComplete;
		}
		
		let formdata = new FormData(document.getElementsByName("report_filter_form")[0])
		let form_values = Object.fromEntries(formdata);//console.log(form_values);
		
		if($scope.rep_cat == "quantities"/* || $scope.rep_cat == "amounts"*/){
			form_values.rep_cols = formdata.getAll('rep_cols');
		}
		
		if(document.querySelectorAll('.filter_type').length > 1/* && document.querySelector('#item').checked*/){
			form_values.filter_type = formdata.getAll('filter_type');
		}
		
		if(checkForm()){
			if($scope.rep_cat == "quantities"){//alert(form_values.from_period);
				if(form_values.rep_cols.length == 4){
					form_values.rep_cols.push("missing");
				}
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
			console.log(form_values, $scope.item_name, $scope.item_cat,$scope.fro_date,$scope.to_date,$scope.to_period, $scope.date);
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
		
	$http.get(getUrl(category), {params: {_data: filter_items}}).then(function(response){
		console.log(response.data);
		createEl();
		/*if(response.data.status_offers == 1){
			createAmountsOffersTable();
			$scope.offers_table_rows = response.data.message_offers;
		}*/
		if(category == "amounts"){
			createAmountsSummaryTable()
			$scope.drinks_sales_total = response.data.drinks_sales_total;
			$scope.drinks_reqs_total = response.data.drinks_reqs_total;
			$scope.eats_sales_total = response.data.eats_sales_total;
			$scope.eats_reqs_total = response.data.eats_reqs_total;
			$scope.total_sales = response.data.total_sales;
			$scope.total_reqs = response.data.total_reqs;
			$scope.total_mm = response.data.total_mm;
			$scope.summary_bal = response.data.summary_bal;
		}
		
		if(category == "accountabilities"){
			$scope.bought_total = response.data.bought_total;
			$scope.kit_bought_subtotal = response.data.kit_bought_subtotal;
			$scope.sold_sale_total = response.data.sold_sale_total;
			$scope.sold_cost_total = response.data.sold_cost_total;
			$scope.offered_cost_total = response.data.offered_cost_total;
			$scope.spoilt_cost_total = response.data.spoilt_cost_total;
		}
		
		$scope.table_rows = response.data.message;
		$scope.offers_table_rows = response.data.message_offers;
		toggleLoader("none");
	},function(response){
		//createEl();
	});
	
	let currMonth = new Date().toLocaleString('default', {month: 'long'});
	let from_date = new Date(filter_items.from_date).toLocaleDateString();
	let to_date = new Date(filter_items.to_date).toLocaleDateString();
	let from_period, to_period;
	
	if(filter_items.from_period){
		/*if(filter_items.from_period == "19:00:00"){
			from_period = "Night";
		}else{
			from_period = "Day";
		}*/from_period = filter_items.from_period;
	}
	
	if(filter_items.to_period){
		/*if(filter_items.to_period == "19:00:00"){
			to_period = "Night";
		}else{
			to_period = "Day";
		}*/to_period = filter_items.to_period;
	}
	
	if(category == "amounts"){
		let report_date = new Date(filter_items.amounts_date).toLocaleDateString();
		$scope.report_desc = `${station} Purchases And Sales Report of ${report_date}`;
	}else if(category == "quantities"){
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
	}else if(category == "accountabilities"){
		let month_name;
		if(filter_items.item_acc_date_filter == "monthly"){
			let date = new Date()
			date.setMonth(Number(filter_items.item_acc_date_filter_month)-1);
			month_name = date.toLocaleString('en', {month: 'long'});//Intl.DateTimeFormat('en', {month: 'long'}).format(new Date('7'));
		}
		$scope.report_desc = `${station} ${filter_items.item_cat} Monthly Accountability For ${month_name} ${filter_items.item_acc_date_filter_year}`;
	}
	
	function getUrl(cat){
		let url;
		
		if(cat == "quantities"){
			url = "../crud/read/getReport.php";
		}else if(cat == "amounts"){
			url = "../crud/read/getAmountsReport.php";
		}else if(cat == "accountabilities"){
			if(filter_items.item_cat == "Drinks"){
				url = "../crud/read/getDrinksAccReport.php";
			}else{
				url = "../crud/read/getEatsKitAccReport.php";
			}
		}
		
		return url;
	}
	
	/*function getParams(cat){
		let params;
		
		if(cat == "quantities"){
			params = "../crud/read/getReport.php";
		}else if(cat == "amounts"){
			params = "../crud/read/getAmountsReport.php";
		}
		
		return params;
	}*/
	
	function createEl(){
		let table = document.createElement('table');
		let thead = document.createElement('thead');
		let tbody = document.createElement('tbody');
		
		//thead.appendChild(createTableHead(getTableCols(category,rep_cols)));
		//thead.appendChild(createQtyTableHead(qtyTableCols(rep_cols)));
		table.appendChild(/*thead*/createTableHead(category, getTableCols(category,rep_cols),thead));
		tbody.appendChild(getTableData(category, getTableCols(category,rep_cols), createTableBody()));
		//tbody.appendChild(createQtyTableData(qtyTableCols(rep_cols), createTableBody()));
		if(category == 'amounts'){createAmountsOffersTable(tbody);}
		if(category == 'amounts'){createAmountsTotalsRow(tbody)}
		if(category == 'accountabilities'){createAccTotalsRow(tbody,filter_items.item_cat)}
		//Dynamically added component should be compiled using angular, use $compile function 
		$compile(tbody)($scope);
		table.appendChild(tbody);
		document.querySelector('#table_content_box').appendChild(table);
	}
	
	
	function createAmountsSummaryTable(){
		let table = document.createElement('table');
		let thead = document.createElement('thead');
		let tbody = document.createElement('tbody');
		
		let head_row = document.createElement('tr');
		let th = document.createElement('th');
		th.setAttribute("colspan", 2);
		let txt = document.createTextNode("Summary");
		th.appendChild(txt);
		head_row.appendChild(th);
		
		thead.appendChild(head_row);
		table.appendChild(thead);
		let salestr = document.createElement('tr');
		let salestxt_td = document.createElement('td');
		salestxt_td.appendChild(document.createTextNode('Total Sales'));
		let salesamnt_td = document.createElement('td');
		salesamnt_td.style.textAlign = "right";
		salesamnt_td.appendChild(document.createTextNode('{{::total_sales}}'));
		salestr.appendChild(salestxt_td);
		salestr.appendChild(salesamnt_td);
		tbody.appendChild(salestr);
				
		let reqstr = document.createElement('tr');
		let reqstxt_td = document.createElement('td');
		reqstxt_td.appendChild(document.createTextNode('Total Purchases'));
		let reqsamnt_td = document.createElement('td');
		reqsamnt_td.style.textAlign = "right";
		reqsamnt_td.appendChild(document.createTextNode('{{::total_reqs}}'));
		reqstr.appendChild(reqstxt_td);
		reqstr.appendChild(reqsamnt_td);
		tbody.appendChild(reqstr);
		
		let baltr = document.createElement('tr');
		let baltxt_td = document.createElement('td')
		baltxt_td.appendChild(document.createTextNode('Balance'));
		let balamnt_td = document.createElement('td');
		balamnt_td.style.textAlign = "right";
		balamnt_td.appendChild(document.createTextNode('{{::summary_bal}}'));
		baltr.appendChild(baltxt_td);
		baltr.appendChild(balamnt_td);
		tbody.appendChild(baltr);
		
		let mmpymnt_tr = document.createElement('tr');
		mmpymnt_tr.style.color = "red";
		let mmpymnt_txt_td = document.createElement('td');
		mmpymnt_txt_td.appendChild(document.createTextNode('Mobile M. Payment'));
		let mmpymnt_amnt_td = document.createElement('td');
		mmpymnt_amnt_td.style.textAlign = "right";
		mmpymnt_amnt_td.appendChild(document.createTextNode('({{::total_mm}})'));
		mmpymnt_tr.appendChild(mmpymnt_txt_td);
		mmpymnt_tr.appendChild(mmpymnt_amnt_td);
		tbody.appendChild(mmpymnt_tr);
		
		
		//Dynamically added component should be compiled using angular, use $compile function 

		$compile(tbody)($scope);
		table.appendChild(tbody);
		document.querySelector('#table_content_box').appendChild(table);
	}
	
	function getTableCols(cat,rep_cols_arr){
		let table_cols;
		if(cat == "quantities"){
			table_cols = qtyTableCols(rep_cols_arr);
		}else if(cat == "amounts"){
			table_cols = amtTableCols(rep_cols_arr);
		}else if(cat == "accountabilities"){
			table_cols = accTableCols(rep_cols_arr);
		}
		return table_cols;
	}
	
	function qtyTableCols(rep_cols_arr){
		let table_cols;
		if(rep_cols_arr.length == 5){
			table_cols = {no_col:"#", item: "Item", Start: "Start"};
		}else{
			table_cols = {no_col:"#", item: "Item"};
		}
			
		if(rep_cols_arr !== undefined){
			rep_cols.forEach((el, idx) =>{
				table_cols[el] = el;
			});
			//rep_cols.map((el) => {table_cols[el] = el;console.log( el);})
		}
		
		if(rep_cols_arr.length == 5){
			table_cols['Finish'] = 'Finish';
		}
		return table_cols;
	}
	
	function amtTableCols(rep_cols_arr){
		let table_cols;
		table_cols = {item: "Item", Qty: "Qty", Amount: "Amount", itemBought: "Item", QtyBought: "Qty", AmountSpent: "Amount",itemSoldEats: "Item", QtySoldEats: "Qty", AmountEats: "Amount", itemBoughtEats: "Item", QtyBoughtEats: "Qty", AmountSpentEats: "Amount"};
		
		if(rep_cols_arr !== undefined){
			rep_cols.forEach((el, idx) =>{
				table_cols[el] = el;console.log("Noth");
			});
			//rep_cols.map((el) => {table_cols[el] = el;console.log( el);})
		}
		
		return table_cols;
	}
	
	function accTableCols(rep_cols_arr){
		let table_cols;
		table_cols = {no_col:'#', item: "Item", QtyBought: "Qty Bought", AmountBought: "Amount", QtySold: "Qty Sold", QtySoldSale: "Qty Sold (Sale Price)", QtySoldCost: "Qty Sold (Cost Price)", QtyOffered: "Qty Offered", QtyOfferedCost: "Qty Offered (Cost Price)", QtySpoilt: "Qty Spoilt", QtySpoiltCost: "Qty Spoilt (Cost Price)"};

		
		if(rep_cols_arr !== undefined){
			rep_cols.forEach((el, idx) =>{
				table_cols[el] = el;console.log("Noth");
			});
			//rep_cols.map((el) => {table_cols[el] = el;console.log( el);})
		}
		
		return table_cols;
	}
	
	function createTableHead(cat, tab_cols_arr, thead){
		if(cat == "amounts"){
			let item_cat_headrow = document.createElement('tr');
			
			let drinks_th = document.createElement('th');
			drinks_th.setAttribute('colspan', 6);
			drinks_th.appendChild(document.createTextNode('Drinks'));
			item_cat_headrow.appendChild(drinks_th);
			let eats_th = document.createElement('th');
			eats_th.setAttribute('colspan', 6);
			eats_th.appendChild(document.createTextNode('Eats'));
			item_cat_headrow.appendChild(eats_th);
			
			thead.appendChild(item_cat_headrow);
			
			let sales_reqs_headrow = document.createElement('tr');
			
			let drinks_sales_th = document.createElement('th');
			drinks_sales_th.setAttribute('colspan', 3);
			drinks_sales_th.appendChild(document.createTextNode('Sales'));
			sales_reqs_headrow.appendChild(drinks_sales_th);
			let drinks_reqs_th = document.createElement('th');
			drinks_reqs_th.setAttribute('colspan', 3);
			drinks_reqs_th.appendChild(document.createTextNode('Purchases'));
			sales_reqs_headrow.appendChild(drinks_reqs_th);
			let eats_sales_th = document.createElement('th');
			eats_sales_th.setAttribute('colspan', 3);
			eats_sales_th.appendChild(document.createTextNode('Sales'));
			sales_reqs_headrow.appendChild(eats_sales_th);
			let eats_reqs_th = document.createElement('th');
			eats_reqs_th.setAttribute('colspan', 3);
			eats_reqs_th.appendChild(document.createTextNode('Purchases'));
			sales_reqs_headrow.appendChild(eats_reqs_th);
			
			thead.appendChild(sales_reqs_headrow);
			
			let head_row = document.createElement('tr');
			for(let table_col in tab_cols_arr){
				let th = document.createElement('th');
				let txt = document.createTextNode(tab_cols_arr[table_col]);
				th.appendChild(txt);
				head_row.appendChild(th);
			}
			thead.appendChild(head_row);
			
			return thead;
		}else if(cat == "accountabilities"){
			let head_row = document.createElement('tr');
			for(let table_col in tab_cols_arr){
				let th = document.createElement('th');
				let txt = document.createTextNode(tab_cols_arr[table_col]);
				th.appendChild(txt);
				head_row.appendChild(th);
			}
			thead.appendChild(head_row);
			
			return thead;
			
		}else{

			let head_row = document.createElement('tr');
			for(let table_col in tab_cols_arr){
				let th = document.createElement('th');

				let txt = document.createTextNode(tab_cols_arr[table_col]);
				th.appendChild(txt);
				head_row.appendChild(th);
			}
			thead.appendChild(head_row);
			
			return thead;
		}

	}
	
	/*function createTableHead(/*cat, tab_cols_arr){
		/*if(cat == "amounts"){
			
		}
		let head_row = document.createElement('tr');
		for(let table_col in tab_cols_arr){
			let th = document.createElement('th');
			let txt = document.createTextNode(tab_cols_arr[table_col]);
			th.appendChild(txt);
			head_row.appendChild(th);
		}
		return head_row;
	}
	
	/*function createAmtTableHead(tab_cols_arr){//Possibly not required until decicing wether amounts will hav different head row
		let head_row = document.createElement('tr');
		for(let table_col in tab_cols_arr){
			let th = document.createElement('th');
			let txt = document.createTextNode(tab_cols_arr[table_col]);
			th.appendChild(txt);
			head_row.appendChild(th);
		}
		return head_row;
	}*/
	
	function createTableBody(){
		let body_row = document.createElement('tr');
		body_row.dataset.ngRepeat = "table_row in table_rows track by $index";//Adding data related attributes
		body_row.classList.add("animate_repeat"/*, "ng-scope"*/);
		
		return body_row;
	}
	
	function getTableData(cat, tab_cols_arr, body_row_el){
		let table_data;
		if(cat == "quantities"){
			table_data = createQtyTableData(tab_cols_arr, body_row_el);
		}else if(cat == "amounts"){
			table_data = createAmtTableData(tab_cols_arr, body_row_el);
		}else if(cat == "accountabilities"){
			table_data = createAccTableData(tab_cols_arr, body_row_el);
		}
		return table_data;
	}
	
	function createQtyTableData(tab_cols_arr, tab_row_el){
		for(let table_col in tab_cols_arr){
			let td = document.createElement('td');
			let txt;
			if(tab_cols_arr[table_col] == "#"){
				txt = document.createTextNode('{{$index+1}}');
			}else{
				 txt = document.createTextNode(`{{table_row.${table_col}}}`);
			}
			if(tab_cols_arr[table_col] !== "#" && tab_cols_arr[table_col] !== "Item"){
				td.classList.add('qty_col');
			}
			if(tab_cols_arr[table_col] == "Start"){
				td.style.fontWeight = 'bold';
			}
			if(tab_cols_arr[table_col] == "missing"){
				td.style.color = 'red';

			}
			if(tab_cols_arr[table_col] == "Finish"){
				td.style.fontWeight = 'bold';
			}
			td.appendChild(txt);
			tab_row_el.appendChild(td);
		}
		return tab_row_el;
	}
	
	function createAmtTableData(tab_cols_arr, tab_row_el){
		for(let table_col in tab_cols_arr){
			let td = document.createElement('td');
			let txt;
			
			txt = document.createTextNode(`{{table_row.${table_col}}}`);
			
			if(tab_cols_arr[table_col] == "Qty"){
				td.classList.add('qty_col');
			}
			if(tab_cols_arr[table_col] == "Amount"){
				td.classList.add('amount_col');
			}
			td.appendChild(txt);
			tab_row_el.appendChild(td);
		}
		
		return tab_row_el;
	}
	
	function createAccTableData(tab_cols_arr, tab_row_el){
		for(let table_col in tab_cols_arr){
			let td = document.createElement('td');
			let txt;
			if(tab_cols_arr[table_col] == "#"){
				txt = document.createTextNode('{{$index+1}}');
			}else{
				txt = document.createTextNode(`{{table_row.${table_col}}}`);
			}
			
			if(tab_cols_arr[table_col] == "Qty Bought" || tab_cols_arr[table_col] == "Qty Sold" || tab_cols_arr[table_col] == "Qty Offered" || tab_cols_arr[table_col] == "Qty Spoilt"){
				td.classList.add('qty_col');
			}
			if(tab_cols_arr[table_col] == "Amount" || tab_cols_arr[table_col] == "Qty Sold (Sale Price)" || tab_cols_arr[table_col] == "Qty Sold (Cost Price)" || tab_cols_arr[table_col] == "Qty Offered (Cost Price)" || tab_cols_arr[table_col] == "Qty Spoilt (Cost Price)"){
				td.classList.add('amount_col');
			}
			td.appendChild(txt);
			tab_row_el.appendChild(td);
		}
		
		return tab_row_el;
	}
	
	function createAmountsOffersTable(tbody){
		//let table = document.createElement('table');
		//let tbody = document.createElement('tbody');
		
		let blank_row = document.createElement('tr');
		let head_row = document.createElement('tr');
		let td = document.createElement('td');
		td.setAttribute("colspan", 12);
		td.style.textAlign = 'center';
		let txt = document.createTextNode("Offers");
		td.appendChild(txt);
		head_row.appendChild(td);
		
		tbody.appendChild(blank_row);
		tbody.appendChild(head_row);
		
		let offers_body_row = document.createElement('tr');
		offers_body_row.dataset.ngRepeat = "offers_table_row in offers_table_rows track by $index";//Adding data related attributes
		offers_body_row.classList.add("animate_repeat"/*, "ng-scope"*/);
		
		let tab_cols = {DrinksItem: 'DrinksItem', DrinksQty: 'DrinksQty', DrinksAmount: 'DrinksAmount', Drinksblnk1: 'Drinksblnk1', Drinksblnk2: 'Drinksblnk2', Drinksblnk3: 'Drinksblnk3', EatsItem: 'EatsItem', EatsQty: 'EatsQty', EatsAmount: 'EatsAmount', Eatsblnk1: 'Eatsblnk1', Eatsblnk2: 'Eatsblnk2', Eatsblnk3: 'Eatsblnk3' };
		
		for(let table_col in tab_cols){
			let td = document.createElement('td');
			let txt;
			
			txt = document.createTextNode(`{{offers_table_row.${table_col}}}`);
			
			if(tab_cols[table_col] == "DrinksQty" || tab_cols[table_col] == "EatsQty"){
				td.classList.add('qty_col');
			}
			if(tab_cols[table_col] == "DrinksAmount" || tab_cols[table_col] == "EatsAmount"){
				td.classList.add('amount_col');
			}
			td.appendChild(txt);
			offers_body_row.appendChild(td);
		}
		
		tbody.appendChild(offers_body_row);
		//table.appendChild(offers_body_row);
		//$compile(tbody)($scope);
		//table.appendChild(tbody);
		//document.querySelector('#table_content_box').appendChild(table);
		
	}
	
	function createAmountsTotalsRow(tbody){
		let totals_row = document.createElement('tr');
		
		let drinks_sales_total_lbl_td = document.createElement('td');
		drinks_sales_total_lbl_td.setAttribute('colspan', 2);
		drinks_sales_total_lbl_td.appendChild(document.createTextNode('Total'));
		totals_row.appendChild(drinks_sales_total_lbl_td);
		let drinks_sales_total_amt_td = document.createElement('td');
		drinks_sales_total_amt_td.appendChild(document.createTextNode('{{::drinks_sales_total}}'));
		drinks_sales_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(drinks_sales_total_amt_td);
		
		let drinks_reqs_total_lbl_td = document.createElement('td');
		drinks_reqs_total_lbl_td.setAttribute('colspan', 2);
		drinks_reqs_total_lbl_td.appendChild(document.createTextNode('Total'));
		totals_row.appendChild(drinks_reqs_total_lbl_td);
		let drinks_reqs_total_amt_td = document.createElement('td');
		drinks_reqs_total_amt_td.appendChild(document.createTextNode('{{::drinks_reqs_total}}'));
		drinks_reqs_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(drinks_reqs_total_amt_td);
		
		let eats_sales_total_lbl_td = document.createElement('td');
		eats_sales_total_lbl_td.setAttribute('colspan', 2);
		eats_sales_total_lbl_td.appendChild(document.createTextNode('Total'));
		totals_row.appendChild(eats_sales_total_lbl_td);
		let eats_sales_total_amt_td = document.createElement('td');
		eats_sales_total_amt_td.appendChild(document.createTextNode('{{::eats_sales_total}}'));
		eats_sales_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(eats_sales_total_amt_td);
		
		let eats_reqs_total_lbl_td = document.createElement('td');
		eats_reqs_total_lbl_td.setAttribute('colspan', 2);
		eats_reqs_total_lbl_td.appendChild(document.createTextNode('Total'));
		totals_row.appendChild(eats_reqs_total_lbl_td);
		let eats_reqs_total_amt_td = document.createElement('td');
		eats_reqs_total_amt_td.appendChild(document.createTextNode('{{::eats_reqs_total}}'));
		eats_reqs_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(eats_reqs_total_amt_td);
		
		totals_row.style.fontWeight = 'bold';
		tbody.appendChild(totals_row);
		return tbody;
	}
	
	function createAccTotalsRow(tbody, item_cat){
		if(item_cat == "Eats"){	
			let subtotals_row = document.createElement('tr');
			
			let subtotal_lbl_td = document.createElement('td');
			subtotal_lbl_td	.setAttribute('colspan', 3);
			
			subtotal_lbl_td.appendChild(document.createTextNode('Kitchen Items SubTotal'));
			subtotals_row.appendChild(subtotal_lbl_td);
			
			let kit_subtotal_amt_td = document.createElement('td');
			kit_subtotal_amt_td.appendChild(document.createTextNode('{{::kit_bought_subtotal}}'));
			kit_subtotal_amt_td.classList.add('amount_col');
			subtotals_row.appendChild(kit_subtotal_amt_td);
			
			subtotals_row.style.fontWeight = 'bold';
			tbody.appendChild(subtotals_row);
		}
		
		let totals_row = document.createElement('tr');
		
		let total_lbl_td = document.createElement('td');
		total_lbl_td.setAttribute('colspan', 2);

		total_lbl_td.appendChild(document.createTextNode('Total'));
		totals_row.appendChild(total_lbl_td);
		
		totals_row.appendChild(document.createElement('td'));
		let bought_total_amt_td = document.createElement('td');
		bought_total_amt_td.appendChild(document.createTextNode('{{::bought_total}}'));
		bought_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(bought_total_amt_td);
		
		totals_row.appendChild(document.createElement('td'));
		let sold_sale_total_amt_td = document.createElement('td');
		sold_sale_total_amt_td.appendChild(document.createTextNode('{{::sold_sale_total}}'));
		sold_sale_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(sold_sale_total_amt_td);
		
		//totals_row.appendChild(document.createTextNode('td'));
		let sold_cost_total_amt_td = document.createElement('td');
		sold_cost_total_amt_td.appendChild(document.createTextNode('{{::sold_cost_total}}'));
		sold_cost_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(sold_cost_total_amt_td);
		
		totals_row.appendChild(document.createElement('td'));
		let offered_cost_total_amt_td = document.createElement('td');
		offered_cost_total_amt_td.appendChild(document.createTextNode('{{::offered_cost_total}}'));
		offered_cost_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(offered_cost_total_amt_td);
		
		totals_row.appendChild(document.createElement('td'));
		let spoilt_cost_total_amt_td = document.createElement('td');
		spoilt_cost_total_amt_td.appendChild(document.createTextNode('{{::spoilt_cost_total}}'));
		spoilt_cost_total_amt_td.classList.add('amount_col');
		totals_row.appendChild(spoilt_cost_total_amt_td);
		
		totals_row.style.fontWeight = 'bold';
		tbody.appendChild(totals_row);

		return tbody;
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
	//$scope.table_rows = [{item: "Mirinda Pineapple", qty_sold: 25}, {item: "Club", qty_sold: 125}]
});
