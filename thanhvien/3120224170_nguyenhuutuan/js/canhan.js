/*
    File canhan.js tạo các tương tác cho trang cá nhân.
    Bao gồm đổi giao diện sáng/tối và sao chép email.
    Kiểm tra bằng cách nhấn nút trên trang web.
*/


// =============================
// CHỨC NĂNG 1:
// ĐỔI GIAO DIỆN SÁNG / TỐI
// =============================


const btnTheme =
document.getElementById("btnTheme");



const currentTheme =
localStorage.getItem("theme");



if(currentTheme === "dark"){


    document.body.classList.add("dark-mode");


    btnTheme.textContent =
    "☀️ Đổi giao diện sáng";


}



btnTheme.addEventListener(
"click",
function(){



    document.body.classList.toggle(
        "dark-mode"
    );



    if(
    document.body.classList.contains(
        "dark-mode"
    )
    ){


        localStorage.setItem(
            "theme",
            "dark"
        );


        btnTheme.textContent =
        "☀️ Đổi giao diện sáng";



    }
    else{


        localStorage.setItem(
            "theme",
            "light"
        );


        btnTheme.textContent =
        "🌙 Đổi giao diện tối";


    }



});





// =============================
// CHỨC NĂNG 2:
// SAO CHÉP EMAIL
// =============================


const btnCopyEmail =
document.getElementById(
    "btnCopyEmail"
);



const message =
document.getElementById(
    "message"
);




btnCopyEmail.addEventListener(
"click",
function(){



    const email =
document.getElementById("userEmail").textContent.trim();



    navigator.clipboard
    .writeText(email)



    .then(
    function(){


        message.textContent =
        "Đã sao chép email thành công!";


    }
    )



    .catch(
    function(){


        message.textContent =
        "Không thể sao chép email.";


    }
    );



});