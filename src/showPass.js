function showPass(){
    var inpType = document.getElementById('password').type;
    if (inpType == 'text'){
        document.getElementById('password').type = 'password';
        document.getElementById('show-pass-btn').src = "../images/hidePassword.png";
    }else{
        document.getElementById('password').type = 'text';
        document.getElementById('show-pass-btn').src = "../images/showPassword.png";
    }

}