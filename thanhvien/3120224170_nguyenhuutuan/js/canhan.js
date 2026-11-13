/*
File: canhan.js
Sinh viên: Nguyễn Hữu Tuân
MSSV: 3120224170

Chức năng:
- Hiển thị đồng hồ thời gian thực, cập nhật mỗi giây
- Sao chép email cá nhân và tự ẩn thông báo sau 3 giây (không dùng style nội tuyến)
- Tự động ghi nhận lượt xem bằng Fetch POST kèm header X-CSRF-Token (không đổi dữ liệu bằng GET)
- Tải dữ liệu kỹ năng qua Fetch POST kèm header X-CSRF-Token, hiển thị bằng textContent
- Xác nhận trước khi xóa lời nhắn lưu bút (thay thế cho onsubmit="..." nội tuyến)
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
  const gio = String(now.getHours()).padStart(2, "0");
  const phut = String(now.getMinutes()).padStart(2, "0");
  const giay = String(now.getSeconds()).padStart(2, "0");

  clock.textContent = `${gio}:${phut}:${giay}`;
}

// =============================
// 2. SAO CHÉP EMAIL
// =============================

const email = "3120224170@udn.edu.vn";
let messageTimeout;

function showMessage(text, isSuccess = true) {
  const message = document.getElementById("message");

  if (!message) {
    return;
  }

  clearTimeout(messageTimeout);

  message.textContent = text;

  // Dùng class CSS thay vì gán message.style.color để không vi phạm CSP
  message.classList.remove("success-message", "error-message");
  message.classList.add(isSuccess ? "success-message" : "error-message");

  messageTimeout = setTimeout(function () {
    message.textContent = "";
    message.classList.remove("success-message", "error-message");
  }, 3000);
}

function fallbackCopyText(text) {
  const textarea = document.createElement("textarea");
  textarea.value = text;

  // Gắn class ẩn thay vì gán textarea.style trực tiếp
  textarea.className = "sr-only-copy";
  document.body.appendChild(textarea);

  textarea.focus();
  textarea.select();

  let success = false;
  try {
    success = document.execCommand("copy");
  } catch (error) {
    success = false;
  }

  document.body.removeChild(textarea);
  return success;
}

async function copyEmail() {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(email);
    } else {
      const success = fallbackCopyText(email);
      if (!success) {
        throw new Error("Không thể sao chép email");
      }
    }

    showMessage("Đã sao chép email thành công!", true);
  } catch (error) {
    showMessage("Không thể sao chép email. Vui lòng thử lại!", false);
  }
}

// =============================
// 3. LẤY MÃ CSRF TỪ THẺ META HOẶC INPUT ẨN
// =============================

function getCsrfToken() {
  const metaCsrf = document.querySelector('meta[name="csrf-token"]');
  if (metaCsrf) {
    const content = metaCsrf.getAttribute("content");
    return content ? content : "";
  }
  const inputCsrf = document.querySelector(
    'input[name="csrf_token"], input[name="_csrf"]',
  );
  return inputCsrf ? inputCsrf.value : "";
}

// =============================
// 4. KHỞI TẠO TRANG
// =============================

document.addEventListener("DOMContentLoaded", function () {
  const csrfToken = getCsrfToken();

  // 4.1. Khởi chạy đồng hồ
  const clock = document.getElementById("clock");
  if (clock) {
    updateClock();
    setInterval(updateClock, 1000);
  }

  // 4.2. Gắn sự kiện sao chép email
  const btnCopyEmail = document.getElementById("btnCopyEmail");
  if (btnCopyEmail) {
    btnCopyEmail.addEventListener("click", copyEmail);
  }

  // 4.3. Ghi nhận lượt xem qua Fetch POST kèm X-CSRF-Token (Tránh thay đổi CSDL bằng GET)
  if (csrfToken) {
    const bodyParams = new URLSearchParams();
    bodyParams.append("hanh_dong", "tang_luot_xem");

    fetch("gioithieu.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-Token": csrfToken,
      },
      body: bodyParams.toString(),
    })
      .then(function (response) {
        return response.ok ? response.json() : null;
      })
      .then(function (data) {
        if (data && typeof data.tong_luot_xem !== "undefined") {
          const tongLuotXemEl = document.getElementById("tongLuotXem");
          if (tongLuotXemEl) {
            tongLuotXemEl.textContent = String(data.tong_luot_xem);
          }
        }
      })
      .catch(function () {});
  }

  // 4.4. Gọi JSON API kỹ năng bằng Fetch POST có header X-CSRF-Token & hiển thị bằng textContent
  const btnFetchApi = document.getElementById("btnFetchApi");
  const apiResultBox = document.getElementById("apiResultBox");

  if (btnFetchApi && apiResultBox) {
    btnFetchApi.addEventListener("click", function () {
      apiResultBox.classList.add("is-visible");
      apiResultBox.textContent = "Đang tải dữ liệu từ API...";

      fetch("gioithieu.php?api=ky_nang", {
        method: "POST",
        headers: {
          "X-CSRF-Token": csrfToken,
          Accept: "application/json",
        },
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Lỗi khi kết nối API");
          }
          return response.json();
        })
        .then(function (data) {
          const mangDuLieu = Array.isArray(data.du_lieu) ? data.du_lieu : [];
          const danhSach = mangDuLieu
            .map(function (item) {
              return `${item.ten_ky_nang} (${item.nhom_ky_nang} - ${item.muc_do}%)`;
            })
            .join(" ; ");
          apiResultBox.textContent = `Kết quả JSON API (${data.tong_so} kỹ năng) của ${data.thanh_vien}: ${danhSach}`;
        })
        .catch(function () {
          apiResultBox.textContent =
            "Không thể tải dữ liệu từ API. Vui lòng thử lại!";
        });
    });
  }

  // 4.5. Gắn sự kiện xác nhận xóa lưu bút (thay cho onsubmit="..." nội tuyến trong HTML)
  const deleteForms = document.querySelectorAll(".form-xoa-luu-but");
  deleteForms.forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!window.confirm("Xóa lời nhắn này khỏi sổ lưu bút?")) {
        event.preventDefault();
      }
    });
  });
});
