document.addEventListener("DOMContentLoaded", function () {
  // Tự động gửi POST ghi nhận lượt xem
  const formTangLuotXem = document.getElementById("form-tang-luot-xem");

  if (formTangLuotXem) {
    formTangLuotXem.submit();
  }
  const nutAnh = document.querySelector(".profile-image-button");
  const anhChanDung = document.querySelector(".profile-image");
  const danhSachKyNang = document.querySelector(".skills");

  // Chức năng phóng to ảnh chân dung
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

      nutDong.type = "button";
      nutDong.textContent = "Đóng";
      nutDong.classList.add("image-modal-close");
      nutDong.setAttribute("aria-label", "Đóng ảnh phóng to");

      lopNen.appendChild(anhPhongTo);
      lopNen.appendChild(nutDong);
      document.body.appendChild(lopNen);

      function dongAnh() {
        lopNen.remove();
        document.removeEventListener("keydown", xuLyPhim);
        nutAnh.focus();
      }

      function xuLyPhim(event) {
        if (event.key === "Escape") {
          dongAnh();
        }
      }

      nutDong.addEventListener("click", dongAnh);

      lopNen.addEventListener("click", function (event) {
        if (event.target === lopNen) {
          dongAnh();
        }
      });

      document.addEventListener("keydown", xuLyPhim);

      nutDong.focus();
    }

    nutAnh.addEventListener("click", moAnh);
  }

  // Chức năng tìm kiếm và lọc kỹ năng
  if (danhSachKyNang) {
    const oTimKiem = document.createElement("input");
    const thongBao = document.createElement("p");

    oTimKiem.type = "search";
    oTimKiem.placeholder = "Tìm kỹ năng...";
    oTimKiem.setAttribute("aria-label", "Tìm kiếm kỹ năng");
    oTimKiem.setAttribute("autocomplete", "off");
    oTimKiem.classList.add("skill-search");

    thongBao.classList.add("skill-message");
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
