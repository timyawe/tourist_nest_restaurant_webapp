//
let displayResponseBox = function(success, response){
		let response_box = document.getElementById("response_box");
		if(success === 1){//
			toggleLoader("none");console.log(response);
			response_box.innerHTML = response
			response_box.style.display = "block";
			response_box.style.backgroundColor = "green";
		}else if(success === 0){
			toggleLoader("none");console.log(response);
			response_box.innerHTML = response;//"An error occured: Record not added, contact Admin";
			response_box.style.display = "block";
			response_box.style.backgroundColor = "red";
		}else if(success === 2){
			toggleLoader("none");
			response_box.innerHTML = response;
			response_box.style.display = "block";
			response_box.style.backgroundColor = "blue";
		}
	}
	
/* Fading out the response */
			
var fadeout = function(){
	if(document.getElementById("response_box").style.display === "block"){
		//document.getElementById("response_box").classList.add("fade_away_el");
		document.getElementById("response_box").className = "fade_away_el";
		document.getElementById("response_box").innerHTML = "";
		
	}
}
	
//Removing response box		
let resetResponseBox = function(){
	document.getElementById("response_box").style.display = "none";
	document.getElementById("response_box").style.backgroundColor = "";
	document.getElementById("response_box").classList.remove("fade_away_el");
}	
	
//Showing and hiding the loader		
let toggleLoader = function(state){
	let loader = document.getElementById("loader");
	loader.style.display = state;
}


function updateActivityLog(_data){
	return fetch ("../crud/create/addActivity.php", {
			method: "POST",
			body: JSON.stringify({userID: _data})
		}).then(function(response){
			return response.text();
		}).then(function(data){
			console.log(data);
			return data;
		});
}
			
//Go back to list of Items after editing
function exitEditMode(btnID){
	document.body.style.cursor = "wait";
	setTimeout(function(){
		//wait for the httpResponse above to finish and return to the orders list
		document.getElementById(btnID).click();
		document.body.style.cursor = "auto";
	}, 8000)
}

/*document.getElementById("print_btn").addEventListener("click", */function printReport(){
	//console.log("print");
	let report_box = document.getElementById("details_box").innerHTML;
	let printWin = window.open('', '', 'height=600', 'width=600');
	//let table_style = document.getElementById("table_print_styles").innerHTML;
	printWin.document.write('<html><head>');
	printWin.document.write('<style type="text/css">');
	//printWin.document.write(table_style);
	printWin.document.write(`table{
		border-collapse: collapse;
		border: 1px solid black;
	}`);
	printWin.document.write(`th {
		height: 30px;
		font-weight: normal;
		border: 2px solid black;
		padding: 3px 5px;
		letter-spacing: 0.15em;
	}`);
	printWin.document.write(`td {
		font-family: arial, sans-serif;
		overflow-y: auto;
		padding: 5px;
		border: 1px solid black;
		white-space: nowrap;
		letter-spacing: 0.5px;
		text-transform: ellipsis;
	}`);
	printWin.document.write(`.qty_col{
		text-align: center;
	}`);
	printWin.document.write(`.amount_col{
		text-align: right;
	}`);
	printWin.document.write('</style>');
	printWin.document.write('</head>');
	printWin.document.write('<body>');
	printWin.document.write(report_box);
	printWin.document.write('</body>');
	printWin.document.write('</html>');
	printWin.document.close();
	
	printWin.print();
	
}		
