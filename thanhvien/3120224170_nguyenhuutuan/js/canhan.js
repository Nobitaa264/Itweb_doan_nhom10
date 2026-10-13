/*
    File: canhan.js
    Chức năng:
    - Hiển thị đồng hồ thời gian thực
    - Sao chép email cá nhân
*/



// =============================
// 1. ĐỒNG HỒ THỜI GIAN THỰC
// =============================


function updateClock() {


    const clock = document.getElementById("clock");


    if (!clock) {
        return;
    }



    const now = new Date();



    let gio = now.getHours();

    let phut = now.getMinutes();

    let giay = now.getSeconds();




    gio = gio < 10 ? "0" + gio : gio;

    phut = phut < 10 ? "0" + phut : phut;

    giay = giay < 10 ? "0" + giay : giay;





    clock.textContent =
        gio + ":" + phut + ":" + giay;


}




// chạy ngay khi mở trang

updateClock();



// cập nhật mỗi giây

setInterval(updateClock,1000);









// =============================
// 2. SAO CHÉP EMAIL
// =============================



const btnCopyEmail =
    document.getElementById("btnCopyEmail");


const message =
    document.getElementById("message");





if(btnCopyEmail){


    btnCopyEmail.addEventListener(
        "click",
        function(){



            const email =
                "3120224170@udn.edu.vn";




            navigator.clipboard.writeText(email)



            .then(function(){



                if(message){


                    message.textContent =
                    "Đã sao chép email thành công!";


                }



            })



            .catch(function(){



                if(message){


                    message.textContent =
                    "Không thể sao chép email.";


                }


            });



        }
    );


}
