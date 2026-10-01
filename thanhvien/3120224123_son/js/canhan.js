/*
  canhan.js - JavaScript cho trang cá nhân của Sơn.
  Tương tác 1: chuyển giao diện sáng/tối và lưu lựa chọn bằng localStorage.
  Tương tác 2: mở/thu gọn phần mục tiêu học tập bằng accordion.
  Cách thử: bấm các nút hoặc dùng Tab và Enter để thao tác.
*/

const nutGiaoDien = document.querySelector("#nut-giao-dien");
const thongBaoGiaoDien = document.querySelector("#thong-bao-giao-dien");

const nutMoRong = document.querySelector("#nut-mo-rong");
const noiDungMoRong = document.querySelector("#noi-dung-mo-rong");

const KHOA_GIAO_DIEN = "giaoDienSon";


function capNhatNutGiaoDien() {
  const dangToi = document.body.classList.contains("che-do-toi");

  nutGiaoDien.setAttribute("aria-pressed", String(dangToi));

  if (dangToi) {
    nutGiaoDien.textContent = "☀️ Chế độ sáng";
  } else {
    nutGiaoDien.textContent = "🌙 Chế độ tối";
  }
}


function taiGiaoDienDaLuu() {
  const giaoDienDaLuu = localStorage.getItem(KHOA_GIAO_DIEN);

  if (giaoDienDaLuu === "toi") {
    document.body.classList.add("che-do-toi");
  }

  capNhatNutGiaoDien();
}


function doiGiaoDien() {
  document.body.classList.toggle("che-do-toi");

  const dangToi = document.body.classList.contains("che-do-toi");

  if (dangToi) {
    localStorage.setItem(KHOA_GIAO_DIEN, "toi");
    thongBaoGiaoDien.textContent = "Đã chuyển sang chế độ tối.";
  } else {
    localStorage.setItem(KHOA_GIAO_DIEN, "sang");
    thongBaoGiaoDien.textContent = "Đã chuyển sang chế độ sáng.";
  }

  capNhatNutGiaoDien();
}


function doiTrangThaiAccordion() {
  const dangMo = nutMoRong.getAttribute("aria-expanded") === "true";
  const trangThaiMoi = !dangMo;

  nutMoRong.setAttribute("aria-expanded", String(trangThaiMoi));
  noiDungMoRong.hidden = !trangThaiMoi;

  if (trangThaiMoi) {
    nutMoRong.textContent = "Thu gọn mục tiêu học tập";
  } else {
    nutMoRong.textContent = "Xem thêm mục tiêu học tập";
  }
}


nutGiaoDien.addEventListener("click", doiGiaoDien);

nutMoRong.addEventListener("click", doiTrangThaiAccordion);

taiGiaoDienDaLuu();