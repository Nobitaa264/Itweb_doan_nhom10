/*
    File canhan.js tạo các tương tác cho trang cá nhân.
    Bao gồm đồng hồ thời gian thực và sao chép email.
    Kiểm tra bằng cách nhấn nút trên trang web.
*/


// =============================
// CHỨC NĂNG 1:
// ĐỒNG HỒ THỜI GIAN THỰC
// =============================


function updateClock() {


    const now = new Date();


    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();



    // Thêm số 0 phía trước nếu nhỏ hơn 10

    hours = hours < 10 ? "0" + hours : hours;

    minutes = minutes < 10 ? "0" + minutes : minutes;

    seconds = seconds < 10 ? "0" + seconds : seconds;



    const time =
        hours + ":" + minutes + ":" + seconds;



    document.getElementById("clock").textContent = time;


}



// Hiển thị ngay khi mở trang

updateClock();



// Cập nhật mỗi giây

setInterval(updateClock, 1000);







// =============================
// CHỨC NĂNG 2:
// SAO CHÉP EMAIL
// =============================



const btnCopyEmail =
document.getElementById("btnCopyEmail");



const message =
document.getElementById("message");





btnCopyEmail.addEventListener(
"click",
function(){


    const email =
    document
    .getElementById("userEmail")
    .textContent
    .trim();



    navigator.clipboard
    .writeText(email)



    .then(function(){


        message.textContent =
        "✅ Đã sao chép email thành công!";


    })



    .catch(function(){


        message.textContent =
        "❌ Không thể sao chép email.";


    });



});
