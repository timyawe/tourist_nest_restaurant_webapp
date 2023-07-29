//Validate form data and submit
let loginBtn = document.getElementById("loginBtn");
let pwd = document.getElementById("pwd");
let usrNm = document.getElementById("usrNm");
let resBox = document.getElementById("response_box");
let staBox = document.getElementsByClassName("sta_box")[0];
let station = document.getElementById("station");
let loader = document.getElementById("loader");

//Disable login button by default; 
//requires both inputs filled to enable
loginBtn.setAttribute("disabled", true);

//Disable password field by default;
//requires username field filled to enable
pwd.setAttribute("disabled", true);
loginBtn.style.cursor = "not-allowed";

//Remove station field by default
//display when user has access level1
//staBox.style.display = "none";

//Enable password if username is not null
usrNm.addEventListener("keyup", function(){
	if(usrNm.value != "" && usrNm.value != " " /*|| usrNm.value != "" && usrNm.value != " " && pwd.value != "" */){
		pwd.removeAttribute("disabled");
		//loginBtn.removeAttribute("disabled");
		//loginBtn.style.cursor = "pointer";
		//pwd.focus();
	}else{
		pwd.setAttribute("disabled", true);
		loginBtn.setAttribute("disabled", true);
		loginBtn.style.cursor = "not-allowed";
	}
});

[pwd, station].forEach(function(element){
	element.addEventListener('keypress', function(e){
		if(e.key === 'Enter'){
			e.preventDefault();
			loginBtn.click();
		}
	})		
})

//Enable login button if both fields are filled
pwd.addEventListener("keyup", function(){
	if(pwd.value != "" && usrNm.value != "" && usrNm.value != " "){
		loginBtn.removeAttribute("disabled");
		loginBtn.style.cursor = "pointer";
	}else{
		loginBtn.setAttribute("disabled", true);
		loginBtn.style.cursor = "not-allowed";
	}	
});


loginBtn.addEventListener("click", function(){
	
	if(pwd.value == "" || usrNm.value == "" || usrNm.value == " "){
		resBox.innerHTML = "All fields are required";
		resBox.style.display = "block";
	}else{
		toggleLoader("block");
		let form_values = {usrNm: usrNm.value, pwd: pwd.value};
			
		loginRequest(form_values).then(res => { //Since fetch returns a promise, use then() here to access the data returned in promise
			if(res !== undefined){
			if(loginBtn.value === "Login"){
				
				if(res.AccessLevel === "Level1"){ //require user to choose station
					usrNm.setAttribute("readonly", true); //to prevent user from changing login details
					pwd.setAttribute("readonly", true); //to prevent user from changing login details
					setTimeout(toggleLoader("none"), 2000);
					staBox.style.display = "flex";
					loginBtn.value = "Continue";
				}else{
					localStorage.setItem("user", JSON.stringify(res));
					setTimeout(toggleLoader("none"), 2000);
					location.replace("/app/home.html");
				}
					//let user = JSON.parse(sessionStorage.getItem("user"));
					//console.log(/*data.message[0]*///user[0]);
			}else{
				if(station.value != ""){
					res.Station = station.value;
					localStorage.setItem("user", JSON.stringify(res));
					setTimeout(toggleLoader("none"), 2000);
					location.replace("/app/home.html");
					//console.log(res, "danku")
				}else{
					setTimeout(toggleLoader("none"), 2000);
					resBox.innerHTML = "Please choose station to continue";
					resBox.style.display = "block";
				}
			}
			}
		});
		
	}
	
});

function loginRequest(_data){
	
	//Use this function to run fetch() 
	//This function is used to store the promise from fetch
	//The function is later used to access the value of the fetch() promise which hopes to be an object of data from server if the promise is successful
	//The purpose of this is to hold the data which is general to every user and later determine which other values to add depending on user category
	
	let msgObj;
	return fetch("../crud/read/loginValidate.php", {
		method: "POST",
		body: JSON.stringify(_data)
	}).then(function(response){
		return response.json();
	}).then(function(data){
		if(data.status === 0){
			setTimeout(toggleLoader("none"), 2000);
			resBox.innerHTML = "Error: Data Access Failed. Try again later";
			resBox.style.display = "block";
		}else if(data.status === 2){
			setTimeout(toggleLoader("none"), 2000);
			resBox.innerHTML = "Username/Password is Incorrect. Please try again";
			resBox.style.display = "block";
		}else{
			resBox.innerHTML = "";
			resBox.style.display = "none";
			msgObj = data.message[0];
		}
		return msgObj;
	});
	
}

function updateSesStorage(val){ //Failed
	let oldData = JSON.parse(sessionStorage.user);
	Object.keys(val).forEach(function(k, v){
		oldData[k] = val[v];
	})
	sessionStorage.setItem("user", JSON.stringify(oldData));
}

function toggleLoader(state){
	loader.style.display = state;
}
