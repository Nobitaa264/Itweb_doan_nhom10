/**
 * TH6 – Bước 12: thêm sản phẩm vào giỏ bằng Fetch API, không tải lại trang.
 * Form không có JavaScript vẫn POST về chi-tiet.php như trước.
 * Backend api/gio-hang.php cùng sử dụng App\Services\GioHang và $_SESSION['gio'].
 */
document.addEventListener("DOMContentLoaded", () => {
  const cacFormThemGio = document.querySelectorAll("form.form-them-gio-hang");
  const nhanSoLuongGio = document.querySelectorAll(".so-luong-gio-hang");

  cacFormThemGio.forEach((form) => {
    const nutGui = form.querySelector('button[type="submit"]');
    const thongBao = document.querySelector("#thong-bao-gio-hang");

    const hienThiThongBao = (noiDung, laLoi = false) => {
      if (!thongBao) return;
      thongBao.textContent = noiDung;
      thongBao.dataset.loai = laLoi ? "loi" : "thanh-cong";
      thongBao.hidden = false;
    };

    form.addEventListener("submit", async (suKien) => {
      suKien.preventDefault();

      if (nutGui?.disabled || !form.reportValidity()) return;

      const id = form.querySelector('[name="san_pham_id"]')?.value?.trim() ?? "";
      const soLuongNhap = form.querySelector('[name="so-luong"]')?.value ?? "";
      // Ưu tiên token của form; nếu thiếu, đọc từ meta dùng chung trong header.
      const csrf = form.querySelector('[name="csrf_token"]')?.value?.trim()
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute("content")?.trim()
        || "";
      const soLuong = Number(soLuongNhap);

      if (!id || !Number.isInteger(soLuong) || soLuong < 1 || soLuong > 99) {
        hienThiThongBao("Mã sản phẩm hoặc số lượng không hợp lệ.", true);
        return;
      }

      if (!csrf) {
        hienThiThongBao("Thiếu mã xác thực phiên. Hãy tải lại trang rồi thử lại.", true);
        return;
      }

      if (nutGui) nutGui.disabled = true;
      if (thongBao) thongBao.hidden = true;

      try {
        const phanHoi = await fetch("api/gio-hang.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-Token": csrf,
          },
          body: JSON.stringify({ san_pham_id: id, so_luong: soLuong }),
        });

        // Nếu máy chủ trả lỗi không phải JSON, vẫn hiện thông báo thay vì tải lại trang.
        const duLieu = await phanHoi.json().catch(() => null);
        if (!phanHoi.ok || duLieu?.thanhCong !== true) {
          hienThiThongBao(
            duLieu?.loi || `Không thể thêm vào giỏ (HTTP ${phanHoi.status}).`,
            true,
          );
          return;
        }

        if (Number.isInteger(duLieu.tongSoLuong)) {
          nhanSoLuongGio.forEach((nhan) => {
            nhan.textContent = String(duLieu.tongSoLuong);
          });
        }
        hienThiThongBao(duLieu.thongBao || "Đã thêm sản phẩm vào giỏ hàng.");
      } catch (loi) {
        console.error("Lỗi khi thêm vào giỏ:", loi);
        hienThiThongBao(
          "Không kết nối được giỏ hàng. Vui lòng kiểm tra mạng và thử lại.",
          true,
        );
        // Không tự submit lại form: request trước đó có thể đã thành công.
      } finally {
        if (nutGui) nutGui.disabled = false;
      }
    });
  });
});
