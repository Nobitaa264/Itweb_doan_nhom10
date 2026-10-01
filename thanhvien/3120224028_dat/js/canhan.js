/*
 * Tệp JavaScript tạo hai tương tác cho trang cá nhân.
 * Tương tác 1: mở ảnh chân dung bằng chuột hoặc bàn phím, đóng bằng nút hoặc Esc.
 * Tương tác 2: nhập từ khóa để lọc danh sách kỹ năng.
 * Có thể kiểm tra bằng chuột, bàn phím (Tab/Enter/Space/Esc) và trên điện thoại.
 */

document.addEventListener("DOMContentLoaded", function () {
  const nutAnh = document.querySelector(".profile-image-button");
  const anhChanDung = document.querySelector(".profile-image");
  const danhSachKyNang = document.querySelector(".skills");

  /*
   * TƯƠNG TÁC 1: XEM ẢNH PHÓNG TO
   * Có thể kích hoạt bằng click hoặc bàn phím Enter/Space.
   */
  if (nutAnh && anhChanDung) {
    function moAnh() {
      if (document.querySelector(".image-modal")) {
        return;
      }

      const lopNen = document.createElement("div");
      const anhPhongTo = document.createElement("img");
      const nutDong = document.createElement("button");

      lopNen.classList.add("image-modal");
      lopNen.setAttribute("role", "dialog");
      lopNen.setAttribute("aria-modal", "true");
      lopNen.setAttribute("aria-label", "Xem ảnh chân dung");

      anhPhongTo.src = anhChanDung.src;
      anhPhongTo.alt = anhChanDung.alt;
      anhPhongTo.classList.add("image-modal-content");

      nutDong.textContent = "Đóng";
      nutDong.classList.add("image-modal-close");
      nutDong.setAttribute("type", "button");
      nutDong.setAttribute("aria-label", "Đóng ảnh phóng to");

      lopNen.appendChild(anhPhongTo);
      lopNen.appendChild(nutDong);
      document.body.appendChild(lopNen);

      nutDong.focus();

      function dongAnh() {
        if (lopNen.parentNode) {
          lopNen.remove();
        }

        nutAnh.focus();
      }

      function xuLyPhim(event) {
        if (event.key === "Escape") {
          dongAnh();
          document.removeEventListener("keydown", xuLyPhim);
        }
      }

      nutDong.addEventListener("click", dongAnh);

      lopNen.addEventListener("click", function (event) {
        if (event.target === lopNen) {
          dongAnh();
          document.removeEventListener("keydown", xuLyPhim);
        }
      });

      document.addEventListener("keydown", xuLyPhim);
    }

    nutAnh.addEventListener("click", moAnh);

    nutAnh.addEventListener("keydown", function (event) {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        moAnh();
      }
    });
  }

  /*
   * TƯƠNG TÁC 2: TÌM KIẾM/LỌC KỸ NĂNG
   */
  if (danhSachKyNang) {
    const oTimKiem = document.createElement("input");
    const thongBao = document.createElement("p");

    oTimKiem.type = "search";
    oTimKiem.placeholder = "Tìm kỹ năng...";
    oTimKiem.setAttribute("aria-label", "Tìm kiếm kỹ năng");
    oTimKiem.setAttribute("autocomplete", "off");
    oTimKiem.classList.add("skill-search");

    thongBao.setAttribute("aria-live", "polite");

    danhSachKyNang.parentNode.insertBefore(oTimKiem, danhSachKyNang);
    danhSachKyNang.parentNode.insertBefore(thongBao, danhSachKyNang);

    function locKyNang() {
      const tuKhoa = oTimKiem.value.trim().toLowerCase();
      const cacKyNang = danhSachKyNang.querySelectorAll("li");

      let soKetQua = 0;

      cacKyNang.forEach(function (kyNang) {
        const tenKyNang = kyNang.textContent.trim().toLowerCase();

        if (tenKyNang.includes(tuKhoa)) {
          kyNang.classList.remove("skill-hidden");
          soKetQua++;
        } else {
          kyNang.classList.add("skill-hidden");
        }
      });

      if (tuKhoa === "") {
        thongBao.textContent = "Hiển thị tất cả kỹ năng.";
      } else {
        thongBao.textContent = "Tìm thấy " + soKetQua + " kỹ năng phù hợp.";
      }
    }

    oTimKiem.addEventListener("input", locKyNang);
  }
});
