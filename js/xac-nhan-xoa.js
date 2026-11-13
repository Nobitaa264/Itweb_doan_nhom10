/*
 * js/xac-nhan-xoa.js
 * Xác nhận thao tác xóa sản phẩm trên trang quản trị.
 * Không xóa sản phẩm khi người dùng chọn Hủy.
 * Không dùng JavaScript nội tuyến, chuẩn bị cho CSP của TH7.
 */

document.querySelectorAll(".form-xoa-san-pham").forEach((form) => {
  form.addEventListener("submit", (event) => {
    const tenSanPham = form.getAttribute("data-ten-san-pham") || "sản phẩm này";
    const dongY = window.confirm(
      `Bạn có chắc chắn muốn xóa sản phẩm "${tenSanPham}"?
Thao tác này không thể hoàn tác.`
    );

    if (!dongY) {
      event.preventDefault();
    }
  });
});
