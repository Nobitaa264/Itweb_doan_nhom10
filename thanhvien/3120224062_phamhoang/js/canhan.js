/*
 * Tệp này tạo hai tương tác cho trang cá nhân.
 * Tương tác 1: đồng hồ đếm ngược đến ngày thi cuối kỳ.
 * Tương tác 2: thu gọn hoặc mở rộng các mục thông tin.
 * Có thể thử bằng chuột hoặc bàn phím trên trang cá nhân.
 */

// ====================
// 1. ĐỒNG HỒ ĐẾM NGƯỢC
// ====================

const countdownElement = document.querySelector("#countdown");

const examDate = new Date("2026-12-20T07:00:00");

function updateCountdown() {
    const now = new Date();
    const remaining = examDate - now;

    if (remaining <= 0) {
        countdownElement.textContent = "Đã đến ngày thi cuối kỳ.";
        return;
    }

    const days = Math.floor(
        remaining / (1000 * 60 * 60 * 24)
    );

    const hours = Math.floor(
        (remaining / (1000 * 60 * 60)) % 24
    );

    const minutes = Math.floor(
        (remaining / (1000 * 60)) % 60
    );

    const seconds = Math.floor(
        (remaining / 1000) % 60
    );

    countdownElement.textContent =
        `${days} ngày ${hours} giờ ${minutes} phút ${seconds} giây`;
}

updateCountdown();

setInterval(updateCountdown, 1000);


// ====================
// 2. ACCORDION
// ====================

const accordionButtons =
    document.querySelectorAll(".accordion-button");

accordionButtons.forEach((button) => {

    button.addEventListener("click", () => {

        const content = button.nextElementSibling;

        const isExpanded =
            button.getAttribute("aria-expanded") === "true";

        button.setAttribute(
            "aria-expanded",
            String(!isExpanded)
        );

        content.hidden = isExpanded;
    });
});