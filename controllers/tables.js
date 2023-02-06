theApp.controller("tablesCtlr", function($scope, $http){
	$http.get("../crud/read/getTables.php").then(function(response){
		//console.log(response.data);
		$scope.tables = response.data;
	}, function(response){
		console.log(response.data);
	});

});

theApp.controller("edit_tableCtlr", function($scope, $timeout, $http, $routeParams, httpResponse){
	/* Pre-select the Status field */
	$scope.status = "1";
	
	/*Generate record for editing */
	if($routeParams.tblID !== undefined){
		$scope.pgtitle = "Edit";
		let tblID = $routeParams.tblID;
		$http.post("../crud/read/getTable.php", {tblID: tblID}).then(function(response){
			if(response.data.status === 1){
				$scope.desc = response.data.message[0].Description;
				$scope.cap = Number(response.data.message[0].Capacity);
				$scope.loc = response.data.message[0].Location;
				$scope.status = response.data.message[0].Status;
			}
			//console.log(response.data.message[0]/*.Capacity*/);
		},function(response){
			console.log(response.data);
		});
		
	}else{
		$scope.pgtitle = "Add";
		//console.log($routeParams.tblID);
	}
	
	/* Validate Form Data and submit */
	$scope.validate = function (){
		let form = document.getElementsByName("table_form")[0];
		let formInputs = new FormData(form);//requires inputs to have `name` attribute
		formInputs.set('desc', capitaliseFirstLetter($scope.desc));
		let form_values = Object.fromEntries(formInputs);
		
		//let form_values = {desc:$scope.desc, capacity:$scope.cap, location:$scope.loc, status:$scope.status};
		//console.log(form_values);
		if($routeParams.tblID === undefined){
			$http.post("../crud/create/add_table.php", form_values).then(function(response){
				httpResponse.success(1, response.data.message);	
			}, function(response){
				httpResponse.error(0, response.data);
				//document.getElementsByClassName("save_btn")[0].
			});
			//console.log("Bakudo #81: Danku");
		}else{
			/*angular.forEach($scope.table_form, function(k, v){
				if(k[0] == '$'){return;}
				console.log(k, v.$pristine);
				
			});*/
			
			/*for(let k of Object.entries(form_values)){ >>> * Check Further <<<
				if(k[0] === 'desc'){
					console.log(k);
				}else{
					var ok = k[0];
					console.log(ok);
					console.log(form_values.ok);
				}
			}*/
			/*if($scope.table_form.loc.$dirty){
				console.log("Dirty");
			}else{
				console.log("Clean");
			}*/
			let editedfields = {tblID: $routeParams.tblID};
			if($scope.table_form.loc.$dirty){
				editedfields.location = $scope.loc;
			}
			if($scope.table_form.desc.$dirty){
				editedfields.description = capitaliseFirstLetter($scope.desc);
			}
			if($scope.table_form.cap.$dirty){
				editedfields.capacity = $scope.cap;
			}
			if($scope.table_form.status.$dirty){
				editedfields.status = $scope.status;
			}
			
			$http.post("../crud/update/setTable.php", editedfields).then(function(response){
				httpResponse.success(1, response.data.message);
			}, function(response){
				httpResponse.error(0, response.data);
			});
			//console.log(editedfields);
			//console.log("Hado #33: Soikatsui", $routeParams.tblID);
		}
		
	}
	

});