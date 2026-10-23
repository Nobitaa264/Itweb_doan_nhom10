document.addEventListener("DOMContentLoaded", () => {
  // CHỈ chọn các form có class hoặc action là thêm vào giỏ hàng
  // Tránh bắt nhầm các form xóa, cập nhật hoặc xóa toàn bộ giỏ
  const cacFormThemGio = document.querySelectorAll(
    'form.form-them-gio-hang, form[action*="them-gio-hang.php"]',
  );
  const nhanSoLuongGio = document.querySelector(".so-luong-gio-hang");

  cacFormThemGio.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      // Chặn hành vi submit form truyền thống
      e.preventDefault();

      const inputId =
        form.querySelector('[name="san_pham_id"]') ||
        form.querySelector('[name="id"]');
      const inputSoLuong =
        form.querySelector('[name="so_luong"]') ||
        form.querySelector('[name="sl"]') ||
        form.querySelector('[name="so-luong"]');

      if (!inputId) return;

      const duLieu = {
        san_pham_id: inputId.value,
        so_luong: inputSoLuong ? parseInt(inputSoLuong.value, 10) : 1,
      };

      try {
        const response = await fetch("api/gio-hang.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(duLieu),
        });

        const resData = await response.json();

        if (response.ok && resData.thanhCong) {
          if (nhanSoLuongGio && resData.tongSoLuong !== undefined) {
            nhanSoLuongGio.textContent = resData.tongSoLuong;
          }
          alert(resData.thongBao || "Đã thêm sản phẩm vào giỏ!");
        } else {
          alert(resData.loi || "Không thể thêm vào giỏ hàng");
        }
      } catch (error) {
        console.error("Lỗi API giỏ hàng:", error);
        // Fallback: Nếu fetch lỗi thì tự submit form truyền thống
        form.submit();
      }
    });
  });
});
