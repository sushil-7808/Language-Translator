function validate(form) {
    let username=form.username.value;
    let password=form.password.value;
    let password_confirm=form.password_confirm.value;

    let submitErr="";
    let submitErrcount=null;

    if(username.length<4){
        if(submitErr){
            submitErr="Username must be at least 4 characters";
        }else{
            submitErr+="\n Username must be at least 4 characters";
        }
        submitErrcount++;  
    }
    if(/[ ]/.test(username)==true){
        if(submitErr){
            submitErr="White space not allowed in username";
        }else{
            submitErr+="\n White space not allowed in username";
        }
    }
    if(/[A-Z]/.test(password)==false){
        if(submitErr){
            submitErr="White space not allowed in username";
        }else{
            submitErr+="\n Password must contain uppercase";
        }
        submitErrcount++;  
    }
    if(/[a-z]/.test(password)==false){
        if(submitErr){
            submitErr="Password must contain lowercase";
        }else{
            submitErr+="\n Password must contain lowercase";
        }
        submitErrcount++;  
    }
    if(/[0-9]/.test(password)==false){
        if(submitErr){
            submitErr="Password must contain number";
        }else{
            submitErr+="\n Password must contain number";
        }
        submitErrcount++;  
    }
    if(password.length<6){
        if(submitErr){
            submitErr="Password must be at least 6 characters";
        }else{
            submitErr+="\n Password must be at least 6 characters";
        }
        submitErrcount++;  
    }
    if(password!=password_confirm){
        if(submitErr){
            submitErr="Passwords do not match";
        }else{
            submitErr+="\n Passwords do not match";
        }
        submitErrcount++;  
    }

    if(submitErrcount){
        alert(submitErr);
        return false;
    }else{
        return true;
    }

    console.log(username);
    // fail = validateUsername(form.username.value) ;
    // fail += validatePassword(form.password);
    // if (fail == "") return true;
    // else { alert(fail); return false; }
    return false;
} 



let choosefileform=()=>{
    const choosefile=document.getElementById('choosefile');
    if(choosefile.files[0]){
        let ext=choosefile.files[0].name.split(".").slice(-1);
        if(ext=='txt'){
            return true;
        }else{
            alert("only txt files are allowed");
            return false;
        }
    }else{
        return false; 
    }
}

let choosefileo=()=>{
    const choosefile=document.getElementById('choosefile');
    const dropzoneData=document.getElementById('data');
    if(choosefile.files[0]){
        let ext=choosefile.files[0].name.split(".").slice(-1);
        if(ext=='txt'){
            let reader=new FileReader();
            reader.onload=function(){
                dropzoneData.value=reader.result;
            }
            reader.readAsText(choosefile.files[0]);
        }
    }
}
let choosefile=document.getElementById('choosefile');
if(choosefile){
    choosefile.addEventListener("change", choosefileo);
}


