/*
  main.js - JavaScript dùng chung cho website StyleHub.
  Điều khiển menu mobile, hỗ trợ mở/đóng bằng nút và đóng bằng phím Esc.
  Khi JavaScript bị tắt, menu vẫn hiển thị theo hướng Progressive Enhancement.
  Cách thử: ở 360px mở menu, nhấn Esc và kiểm tra aria-expanded trở về false.
*/

const nutMenu =
  document.querySelector("#nut-menu");

const danhSachMenu =
  document.querySelector("#danh-sach-menu");


function capNhatTrangThaiMenu(dangMo) {
  if (
    nutMenu === null ||
    danhSachMenu === null
  ) {
    return;
  }

  nutMenu.setAttribute(
    "aria-expanded",
    String(dangMo)
  );

  danhSachMenu.classList.toggle(
    "menu-chinh__danh-sach--mo",
    dangMo
  );

  if (dangMo) {
    nutMenu.textContent =
      "Đóng menu";
  } else {
    nutMenu.textContent =
      "Menu";
  }
}


function xuLyMenu() {
  if (
    nutMenu === null ||
    danhSachMenu === null
  ) {
    return;
  }

  const dangMo =
    nutMenu.getAttribute(
      "aria-expanded"
    ) === "true";

  capNhatTrangThaiMenu(
    !dangMo
  );
}


function xuLyPhimMenu(event) {
  if (
    event.key !== "Escape" ||
    nutMenu === null ||
    danhSachMenu === null
  ) {
    return;
  }

  const dangMo =
    nutMenu.getAttribute(
      "aria-expanded"
    ) === "true";

  if (dangMo) {
    capNhatTrangThaiMenu(false);

    nutMenu.focus();
  }
}


if (
  nutMenu !== null &&
  danhSachMenu !== null
) {
  document.documentElement.classList.add(
    "co-js"
  );

  nutMenu.addEventListener(
    "click",
    xuLyMenu
  );

  document.addEventListener(
    "keydown",
    xuLyPhimMenu
  );

  capNhatTrangThaiMenu(false);
}