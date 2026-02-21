document.getElementById("uname").addEventListener("input", ()=>{
    const val = document.getElementById("uname").value;
    const euname = document.getElementById("euname");
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
        euname.innerHTML = "<br>Only Alphabets are allowed";
        euname.style.color = "Red";
        sub.disabled = true;
    } else{
        euname.innerHTML = "";
        sub.disabled = false;
    }
});

