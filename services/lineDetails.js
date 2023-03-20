//Create Angular custom service for manipulating order/requisition line details
theApp.service("lineDetails", function($http){

	this.getItems = function(url, params){
		return $http.get(url, params).then(function(response){
			return response.data;
		});
	}
	
	this.checkItem = function(row, idx, line_details_arr, rows_arr, category){//x
		if(row.item !== undefined){
			if(category == 'order'){
				applyRate(category);
			}else{
				let finish = Number(row.item.finish);
				let minstock = Number(row.item.MinStockLevel);
				let isRecieved = Number(row.item.Recieved);
				let item = row.item.label;
				if(!isRecieved){
					alert(`${item} was previously requested for but not recieved, recieve item insted`);
					row.item = undefined;
				}else{
					if(finish > minstock || !minstock ){
						alert(`${item} doesn't require restocking`);
						row.item = undefined;
					}else{
						applyRate(category);
					}
				}
			}
		}else{
			row.qty = null;
			row.rate = null;
			row.total = null;
			row.purchaseAmount = null;
			line_details_arr.splice(idx, 1);
		}
		
		function applyRate(category){
			if(line_details_arr.length > 0){
				if(!comparePdts(row.item, line_details_arr)){
					//adding the order details' objects to array by the current index
					if(category != 'order'){
						line_details_arr[idx] = {pdtNo:row.item.value, purchaseAmount:row.purchaseAmount};
					}else{
						line_details_arr[idx] = {pdtNo:row.item.value};
					}
					
					//Apply the total if the qty field is already filled
					if(row.qty !== null){
						this.checkQty(row,idx,line_details_arr);
					}
					//console.log(line_details_arr);
				}else{
					alert("Item is already selected, adjust its Qty instead");
					rows_arr.splice(idx, 1);
					line_details_arr.splice(idx, 1);
					console.log(line_details_arr);
				}
			}else{
				//adding the order details' objects to array by the current index
				if(category != 'order'){
					line_details_arr[idx] = {pdtNo:row.item.value, purchaseAmount:row.purchaseAmount};
				}else{
					line_details_arr[idx] = {pdtNo:row.item.value};
				}
				
				//Apply the total if the qty field is already filled
				if(row.qty !== null){
					this.checkQty(row,idx,line_details_arr);
				}
				console.log(line_details_arr, row.item);
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
	
	this.checkQty = function(row, idx, line_details_arr, category){
		let qty = row.qty;
		let finish = Number(row.item.finish);
		
		if(category == 'order'){
			if(qty > finish){
				alert("Cannot update Qty, quantity entered is more than quantity available for sale");
				row.qty = null;
			}else{
				computeSubTotal(row,idx,line_details_arr);
			}
		}else{
			computeSubTotal(row,idx,line_details_arr);
		}
	}
	
	/*this.computeSubTotal =*/ function computeSubTotal(/*qty,item*/row, idx, line_details_arr){
		//let total_input = document.getElementsByClassName("total_input");
		let item = row.item;
		let qty = row.qty;
		
		if(item !== undefined && qty !== null ){
			if(qty > 0){
				row.total = qty * item.rate;
				
				//adding the requisition details' objects' properties by the current index
				line_details_arr[idx].qty = qty;
				line_details_arr[idx].rate = item.rate;
				line_details_arr[idx].subtotal = qty * item.rate;
				console.log(line_details_arr);
			}else{
				alert("Qty should atleast be 1");
				row.qty = null;
			}
			/*for(let x = 0; x < total_input.length; x++){
				if(idx === x){
					total_input[x].value = qty * item.rate;
					
					//adding the requisition details' objects' properties by the current index
					line_details_arr[idx].qty = qty;
					line_details_arr[idx].rate = item.rate;
					line_details_arr[idx].subtotal = qty * item.rate;
					console.log(line_details_arr);
				}
			}*/
		}
	}
	
	this.addRow_create = function(arr, items_arr){
		let counter = arr.length + 1;
		if(arr.length != items_arr.length){
			alert("Fill available rows before adding another");
		}else{
			arr.push({ID: counter, item: undefined, qty:null, rate:null, total:null, purchaseAmount:null, itemSelected: false});
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