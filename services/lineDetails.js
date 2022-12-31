//Create Angular custom service for manipulating order/requisition line details
theApp.service("lineDetails", function($http){

	this.getItems = function(url){
		return $http.get(url).then(function(response){
			return response.data;
		});
	}
	
	this.applyRate = function(x, idx, line_details_arr, rows_arr){
		let qty_input = document.getElementsByClassName("qty_input");
		let rate_input = document.getElementsByClassName("rate_input");
		let total_input = document.getElementsByClassName("total_input");
		
		for(let i = 0; i<rate_input.length; i++){
			if(idx === i){
				if(x !== undefined){
					if(line_details_arr.length > 0){
						if(!comparePdts(x, line_details_arr)){
							//applyRateProcess(rate_input[i].value, x.rate, line_details_arr[idx], x.value);
							rate_input[i].value = x.rate;
					
							line_details_arr[idx] = {pdtNo:x.value};//adding the order details' objects to array by the current index
							
							//Apply the total if the qty field is already filled
							if(qty_input[i].value !== undefined){
								let qty = qty_input[i].value;
								console.log(qty_input[i].value);
								this.computeSubTotal(qty,x,idx,line_details_arr);
							}
						}else{
							alert("Item is already selected, adjust its Qty instead");
							rows_arr.splice(idx, 1);
							line_details_arr.splice(idx, 1);
							console.log(line_details_arr);
						}
					
					}else{
						//applyRateProcess(rate_input[i].value, x.rate, line_details_arr[idx], x.value);
						rate_input[i].value = x.rate;
						
						line_details_arr[idx] = {pdtNo:x.value};//adding the order details' objects to array by the current index
						
						//Apply the total if the qty field is already filled
						if(qty_input[i].value !== undefined){
							let qty = qty_input[i].value;
							console.log(qty_input[i].value);
							this.computeSubTotal(qty,x,idx,line_details_arr);
						}
					}
					
				}else{
					qty_input[i].value = "";
					rate_input[i].value = "";
					total_input[i].value = "";
				}
			}
		}
	}
	
	/*this.comparePdts = */function comparePdts(item_sel, arr_to_comp){
		//return function(item_sel, arr_to_comp){
			let equal = false;
			angular.forEach(arr_to_comp, function(v){
				if(v.pdtNo === item_sel.value){
					equal = true;
				}
			});
			return equal;
		//}
	}
	
	function applyRateProcess(input_val, item_rate, arr,item_value){
		input_val = item_rate;
					
		arr = {pdtNo:item_value};//adding the order details' objects to array by the current index
		
		//Apply the total if the qty field is already filled
		/*if(qty_input[i].value !== undefined){//Throws error
			let qty = qty_input[i].value;
			this.computeSubTotal(qty,x,idx);
		}*/
	}
	
	this.computeSubTotal = function(qty,item, idx, line_details_arr){
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
	}
	
	this.addRow_create = function(arr, items_arr){
		let counter = arr.length + 1;
		if(arr.length != items_arr.length){
			alert("Fill available rows before adding another");
		}else{
			arr.push({ID: counter});
		}
	}
	
	this.addRow_edit = function(arr, edit, items_arr){
		let counter = (arr.length + 1) + items_arr.length;
		if(!edit){
			edit = true;
			arr.push({ID: counter});
		}else{
			arr.push({ID: counter});console.log(edit);
		}
	}
	
	this.removeRow_create = function(arr, index, msg, items_arr){
		if(arr.length === 1){
			alert(msg);
		}else{
			arr.splice(index, 1);
			items_arr.splice(index, 1);
		}
	}

});