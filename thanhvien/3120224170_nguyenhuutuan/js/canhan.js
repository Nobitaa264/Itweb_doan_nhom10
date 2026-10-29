/*
File: canhan.js
Sinh viên: Nguyễn Hữu Tuân
MSSV: 3120224170

Chức năng:
- Hiển thị đồng hồ thời gian thực
- Cập nhật đồng hồ mỗi giây
- Sao chép email cá nhân
- Hiển thị thông báo khi sao chép
- Tự động ẩn thông báo sau 3 giây
- Tải dữ liệu kỹ năng qua Fetch API
*/

// =============================
// 1. ĐỒNG HỒ THỜI GIAN THỰC
// =============================

function updateClock() {
  const clock = document.getElementById("clock");

  // Kiểm tra phần tử đồng hồ có tồn tại
  if (!clock) {
    return;
  }

  // Lấy thời gian hiện tại trên thiết bị
  const now = new Date();

  const gio = String(now.getHours()).padStart(2, "0");

  const phut = String(now.getMinutes()).padStart(2, "0");

  const giay = String(now.getSeconds()).padStart(2, "0");

  // Hiển thị theo định dạng HH:MM:SS
  clock.textContent = `${gio}:${phut}:${giay}`;
}

// =============================
// 2. SAO CHÉP EMAIL
// =============================

// Email cá nhân
const email = "3120224170@udn.edu.vn";

// Bộ hẹn giờ ẩn thông báo
let messageTimeout;

// Hàm hiển thị thông báo
function showMessage(text, isSuccess = true) {
  const message = document.getElementById("message");

  if (!message) {
    return;
  }

  // Xóa bộ hẹn giờ cũ nếu có
  clearTimeout(messageTimeout);

  message.textContent = text;

  // Đổi màu theo kết quả
  message.style.color = isSuccess ? "#15803d" : "#dc2626";

  // Tự động ẩn sau 3 giây
  messageTimeout = setTimeout(function () {
    message.textContent = "";
  }, 3000);
}

// Hàm sao chép dự phòng
function fallbackCopyText(text) {
  const textarea = document.createElement("textarea");

  textarea.value = text;

  // Đưa phần tử ra ngoài vùng nhìn thấy
  textarea.style.position = "fixed";
  textarea.style.left = "-9999px";

  document.body.appendChild(textarea);

  textarea.focus();
  textarea.select();

  let success = false;

  try {
    success = document.execCommand("copy");
  } catch (error) {
    success = false;
  }

  // Xóa phần tử tạm
  document.body.removeChild(textarea);

  return success;
}

// Hàm xử lý sao chép email
async function copyEmail() {
  try {
    // Dùng Clipboard API nếu trình duyệt hỗ trợ
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(email);
    } else {
      // Sao chép bằng phương án dự phòng
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
// 3. KHỞI TẠO TRANG
// =============================

document.addEventListener("DOMContentLoaded", function () {
  // Khởi chạy đồng hồ
  const clock = document.getElementById("clock");

  if (clock) {
    // Hiển thị ngay khi tải trang
    updateClock();

    // Cập nhật mỗi giây
    setInterval(updateClock, 1000);
  }

  // Gắn sự kiện sao chép email
  const btnCopyEmail = document.getElementById("btnCopyEmail");

  if (btnCopyEmail) {
    btnCopyEmail.addEventListener("click", copyEmail);
  }

  // Gắn sự kiện gọi JSON API bằng Fetch (Phần B)
  const btnFetchApi = document.getElementById("btnFetchApi");
  const apiResultBox = document.getElementById("apiResultBox");

  if (btnFetchApi && apiResultBox) {
    btnFetchApi.addEventListener("click", function () {
      apiResultBox.style.display = "block";
      apiResultBox.textContent = "Đang tải dữ liệu từ API...";

      fetch("gioithieu.php?api=ky_nang")
        .then(function (response) {
          if (!response.ok) {
            throw new Error("Lỗi khi kết nối API");
          }
          return response.json();
        })
        .then(function (data) {
          const danhSach = (data.du_lieu || [])
            .map(function (item) {
              return `${item.ten_ky_nang} (${item.nhom_ky_nang} - ${item.muc_do}%)`;
            })
            .join(" | ");
          apiResultBox.textContent = `Kết quả JSON API (${data.tong_so} kỹ năng) của ${data.thanh_vien}: ${danhSach}`;
        })
        .catch(function (error) {
          apiResultBox.textContent =
            "Không thể tải dữ liệu từ API. Vui lòng thử lại!";
        });
    });
  }
});
