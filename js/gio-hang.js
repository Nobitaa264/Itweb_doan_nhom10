document.addEventListener("DOMContentLoaded", () => {
  const cacFormThemGio = document.querySelectorAll(
    'form[action*="gio-hang.php"]',
  );
  const nhanSoLuongGio = document.querySelector(".so-luong-gio-hang");

  cacFormThemGio.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      // Chặn hành vi submit form truyền thống (chỉ chạy khi bật JS)
      e.preventDefault();

      const inputId =
        form.querySelector('[name="san_pham_id"]') ||
        form.querySelector('[name="id"]');
      const inputSoLuong =
        form.querySelector('[name="so_luong"]') ||
        form.querySelector('[name="sl"]');

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
        // Fallback: Nếu fetch lỗi, tự submit form truyền thống
        form.submit();
      }
    });
  });
});
