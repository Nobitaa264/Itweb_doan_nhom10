document.addEventListener("DOMContentLoaded", () => {
  const oTimKiem = document.querySelector('input[name="q"]');
  const vungKetQua =
    document.querySelector(".danh-sach-san-pham") ||
    document.querySelector(".khu-vuc-danh-sach");

  if (!oTimKiem || !vungKetQua) return;

  let timerDebounce = null;

  oTimKiem.addEventListener("input", (e) => {
    const tuKhoa = e.target.value.trim();

    // Kỹ thuật Debounce 300ms
    clearTimeout(timerDebounce);
    timerDebounce = setTimeout(() => {
      xuLyTimKiem(tuKhoa);
    }, 300);
  });

  async function xuLyTimKiem(tuKhoa) {
    try {
      const response = await fetch(
        `api/san-pham.php?q=${encodeURIComponent(tuKhoa)}`,
        {
          headers: { Accept: "application/json" },
        },
      );

      if (!response.ok) throw new Error("Lỗi tải dữ liệu");

      const data = await response.json();
      hienThiKetQua(data.danhSach || data);
    } catch (error) {
      console.error("Lỗi API tìm kiếm:", error);
    }
  }

  function hienThiKetQua(danhSach) {
    if (!Array.isArray(danhSach) || danhSach.length === 0) {
      vungKetQua.innerHTML =
        '<p class="thong-bao-rong">Không tìm thấy sản phẩm phù hợp.</p>';
      return;
    }

    vungKetQua.innerHTML = danhSach
      .map(
        (sp) => `
            <article class="the-san-pham">
                <img class="the-san-pham__anh" src="${sp.hinhAnh || sp.hinh_anh}" alt="${sp.ten}" width="300" height="300">
                <div class="the-san-pham__noi-dung">
                    <p class="the-san-pham__ma">${sp.id}</p>
                    <h3 class="the-san-pham__ten">${sp.ten}</h3>
                    <p class="the-san-pham__gia">${new Intl.NumberFormat("vi-VN").format(sp.gia)}₫</p>
                    <a class="nut nut-phu" href="chi-tiet.php?id=${encodeURIComponent(sp.id)}">Xem chi tiết</a>
                </div>
            </article>
        `,
      )
      .join("");
  }
});
