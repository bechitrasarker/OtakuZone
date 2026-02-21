
document.getElementById("fname").addEventListener("input", ()=>{
    const val = document.getElementById("fname").value;
    const efname = document.getElementById("efname");
    let flag = 1;
    for(let i = 0; i < val.length; i++){
        if((val[i]>='A' && val[i]<='Z') || (val[i]>='a' && val[i]<='z') || val[i]==' '){
            flag = 1;
            continue;
        }
        flag = 0;
        break;
    }
    if(flag == 0){
        efname.innerHTML = "Only Alphabets are allowed"; 
        efname.style.color = "Red";
        sub.disabled = true;
    } else {
        efname.innerHTML = "";
        sub.disabled = false;
    }
});

document.getElementById("lname").addEventListener("input", ()=>{
    const val = document.getElementById("lname").value;
    const elname = document.getElementById("elname");
    let flag = 1;
    for(let i = 0; i < val.length; i++){
        if((val[i]>='A' && val[i]<='Z') || (val[i]>='a' && val[i]<='z') || val[i]==' '){
            flag = 1;
            continue;
        }
        flag = 0;
        break;
    }
    if(flag == 0){
        elname.innerHTML = "Only Alphabets are allowed";
        elname.style.color = "Red";
        sub.disabled = true;
    } else {
        elname.innerHTML = "";
        sub.disabled = false;
    }
});

document.getElementById("email").addEventListener("input", ()=>{
    const val = document.getElementById("email").value;
    const eemail = document.getElementById("eemail");
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (val === "") {
        eemail.innerText = "";
    } 
    else if (!regex.test(val)) {
        eemail.innerText = "Invalid email format";
        eemail.style.color = "red";
        sub.disabled = true;
    } 
    else {
        eemail.innerText = "";
        sub.disabled = false;   
    }
});

function validategender() {
    const val = document.getElementsByName('gender');
    const egender = document.getElementById('egender');
    let x = false;

    for (let i = 0; i < val.length; i++) {
        if (val[i].checked) {
            x = true;
            break;
        }
    }
    if (!x) {
        egender.innerHTML = "Please select a gender";
        egender.style.color = "red"; 
        return false;
    } else {
        egender.innerHTML = "";
        return true;
    }
}

function clearError() {
    document.getElementById('egender').innerHTML = "";
}

document.getElementById("pass").addEventListener("input", ()=>{
    let val = document.getElementById("pass").value;
    let eca = document.getElementById("eca");
    let esa = document.getElementById("esa");
    let ed = document.getElementById("ed");
    let esc = document.getElementById("esc");
    let el = document.getElementById("el");


    let ca = 0, sa = 0, d = 0, sc = 0;

    for(let i = 0; i<val.length; i++){
        if(val[i]>='A' && val[i]<='Z'){
            ca = 1;
        } else if(val[i]>='a' && val[i]<='z'){
            sa = 1;
        } else if(val[i]>='0' && val[i]<='9'){
            d = 1;
        } else if(val[i]==' '){
            continue;
        } else{
            sc = 1;
        }
    }

    if(val.length<5){
        el.innerHTML = "<br>❌ Must be at least 5 characters";
    }else{
        el.innerHTML = "<br>✔️ At least 5 characters";
    }

    if(ca == 1){
        eca.innerHTML = "<br>✔️Capital Alphabet";
    } else{
        eca.innerHTML = "<br>❌Capital Alphabet";
    }

    if(sa == 1){
        esa.innerHTML = "<br>✔️Small Alphabet";
    } else{
        esa.innerHTML = "<br>❌Small Alphabet";
    }

    if(d == 1){
        ed.innerHTML = "<br>✔️Digit";
    } else{
        ed.innerHTML = "<br>❌Digit";
    }

    if(sc == 1){
        esc.innerHTML = "<br>✔️Special Characters";
    } else{
        esc.innerHTML = "<br>❌Special Characters";
    }
});

document.getElementById("cpass").addEventListener("input", () => {
    let pass = document.getElementById("pass").value;
    let cpass = document.getElementById("cpass").value;
    let ecpass = document.getElementById("ecpass");

    // Check if the confirmation matches the original password
    if (cpass === pass && cpass.length > 0) {
        ecpass.innerHTML = "<br>✔️ Passwords match";
        ecpass.style.color = "green";
    } else if (cpass.length === 0) {
        ecpass.innerHTML = ""; // Keep it empty if they haven't typed yet
    } else {
        ecpass.innerHTML = "<br>❌ Passwords do not match";
        ecpass.style.color = "red";
    }
});