/*
 * TH6 - Tìm kiếm bằng Fetch API cho danh-sach.php (StyleHub - Nhóm 10).
 * - Bắt đúng #tim-kiem và #danh-sach-san-pham từ trang PHP.
 * - Đọc dữ liệu JSON tại duLieu (API sản phẩm của nhóm).
 * - Debounce 350 ms; loại yêu cầu cũ bằng AbortController.
 * - Vẫn giữ form GET khi nhấn Enter/Tìm kiếm và phân trang PHP khi chưa gõ.
 * - Kết quả động có phân trang 10 sản phẩm, giữ lọc và yêu thích.
 * - Tạo DOM an toàn bằng textContent; không dùng innerHTML với dữ liệu API.
 */

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector('body.danh-sach form[action="danh-sach.php"]');
  const oTimKiem = form?.querySelector("#tim-kiem");
  const luoi = document.querySelector("#danh-sach-san-pham");
  const thongBaoRong = document.querySelector("#thong-bao-khong-co-san-pham");
  const trangThaiFetch = document.querySelector("#trang-thai-fetch");
  const soLuongKetQua = document.querySelector("#so-luong-ket-qua");
  const phanTrangGoc = document.querySelector(".th6-phan-trang-danh-sach");
  const oDanhMuc = form?.querySelector("#loc-danh-muc");
  const oGiaMax = form?.querySelector("#gia-toi-da");
  const oSapXep = form?.querySelector("#sap-xep");

  // Trang yêu thích không có form tìm kiếm. Trang không có kết quả vẫn có lưới.
  if (!form || !oTimKiem || !luoi || !thongBaoRong || !soLuongKetQua) return;

  const soMoiTrang = 10;
  let boHenGio = null;
  let yeuCau = null;
  let lanTimKiem = 0;
  let danhSachDaLoc = [];
  let trangDangXem = 1;

  // Lúc chưa tìm kiếm bằng fetch, giữ nguyên danh sách và phân trang PHP.
  const phanTrangMoi = document.createElement("nav");
  phanTrangMoi.className = "th6-phan-trang-danh-sach th6-phan-trang-live";
  phanTrangMoi.setAttribute("aria-label", "Phân trang tìm kiếm trực tiếp");
  phanTrangMoi.hidden = true;
  if (phanTrangGoc) phanTrangGoc.after(phanTrangMoi);
  else luoi.after(phanTrangMoi);

  function taoPhanTu(tenThe, tenClass, noiDung) {
    const the = document.createElement(tenThe);
    if (tenClass) the.className = tenClass;
    if (noiDung !== undefined) the.textContent = String(noiDung ?? "");
    return the;
  }

  function taoTheSanPham(sp) {
    const id = String(sp.id ?? "");
    const ten = String(sp.ten ?? "Sản phẩm");
    const the = taoPhanTu("article", "the-san-pham");
    the.dataset.idSanPham = id;

    const hinh = taoPhanTu("img", "the-san-pham__anh");
    hinh.src = String(sp.hinhAnh ?? "");
    hinh.alt = ten;
    hinh.width = 300;
    hinh.height = 300;
    hinh.loading = "lazy";

    const noiDung = taoPhanTu("div", "the-san-pham__noi-dung");
    const ma = taoPhanTu("p", "ma-san-pham", id);
    const tieuDe = taoPhanTu("h3", "", ten);
    const moTa = taoPhanTu("p", "", sp.moTa ?? "");
    const gia = taoPhanTu(
      "p", "gia", `${Number(sp.gia ?? 0).toLocaleString("vi-VN")}đ`
    );

    const nhomNut = taoPhanTu("div", "nhom-hanh-dong-san-pham");
    const xem = taoPhanTu("a", "nut", "Xem chi tiết");
    xem.href = `chi-tiet.php?id=${encodeURIComponent(id)}`;

    // yeu-thich.js xử lý bằng event delegation và đọc data-id-san-pham.
    const yeuThich = taoPhanTu("button", "nut nut-yeu-thich", "♡ Yêu thích");
    yeuThich.type = "button";
    yeuThich.dataset.idSanPham = id;

    nhomNut.append(xem, yeuThich);
    noiDung.append(ma, tieuDe, moTa, gia, nhomNut);
    the.append(hinh, noiDung);
    return the;
  }

  function taoNutTrang(nhan, trang, voHieu = false) {
    const nut = taoPhanTu("button", "", nhan);
    nut.type = "button";
    nut.disabled = voHieu;
    nut.addEventListener("click", () => {
      trangDangXem = trang;
      hienThiKetQua();
      document.getElementById("tieu-de-ket-qua")?.scrollIntoView({ block: "start" });
    });
    return nut;
  }

  function hienThiKetQua() {
    const tong = danhSachDaLoc.length;
    const tongTrang = Math.max(1, Math.ceil(tong / soMoiTrang));
    trangDangXem = Math.max(1, Math.min(trangDangXem, tongTrang));
    const batDau = (trangDangXem - 1) * soMoiTrang;
    const hienThi = danhSachDaLoc.slice(batDau, batDau + soMoiTrang);

    // Không đưa HTML từ API vào DOM bằng innerHTML (phòng XSS).
    luoi.replaceChildren(...hienThi.map(taoTheSanPham));
    thongBaoRong.hidden = tong !== 0;
    soLuongKetQua.textContent = tong > 0
      ? `${tong} sản phẩm · Trang ${trangDangXem}/${tongTrang}`
      : "0 sản phẩm";

    if (phanTrangGoc) phanTrangGoc.hidden = true;
    phanTrangMoi.replaceChildren();
    phanTrangMoi.hidden = tongTrang <= 1;
    if (tongTrang > 1) {
      phanTrangMoi.append(taoNutTrang("← Trước", trangDangXem - 1, trangDangXem === 1));
      for (let so = 1; so <= tongTrang; so++) {
        if (so === trangDangXem) {
          const hienTai = taoPhanTu("span", "th6-trang-hien-tai", so);
          hienTai.setAttribute("aria-current", "page");
          phanTrangMoi.append(hienTai);
        } else {
          phanTrangMoi.append(taoNutTrang(so, so));
        }
      }
      phanTrangMoi.append(taoNutTrang("Tiếp →", trangDangXem + 1, trangDangXem === tongTrang));
    }

    window.dispatchEvent(new Event("stylehub:yeu-thich-thay-doi"));
  }

  function apDungBoLoc(danhSach) {
    const danhMuc = oDanhMuc?.value || "tat-ca";
    const giaMaxNhap = oGiaMax?.value.trim() ?? "";
    const giaMax = giaMaxNhap === "" ? null : Number(giaMaxNhap);
    let ds = danhSach.filter(sp => {
      if (danhMuc !== "tat-ca" && sp.danhMuc !== danhMuc) return false;
      if (giaMax !== null && Number.isFinite(giaMax) && Number(sp.gia) > giaMax) return false;
      return true;
    });

    const sapXep = oSapXep?.value ?? "mac-dinh";
    if (sapXep === "gia-tang") ds.sort((a, b) => Number(a.gia) - Number(b.gia));
    if (sapXep === "gia-giam") ds.sort((a, b) => Number(b.gia) - Number(a.gia));
    if (sapXep === "ten-a-z") ds.sort((a, b) => String(a.ten).localeCompare(String(b.ten), "vi"));
    if (sapXep === "ten-z-a") ds.sort((a, b) => String(b.ten).localeCompare(String(a.ten), "vi"));
    return ds;
  }

  async function timKiemTrucTiep() {
    lanTimKiem += 1;
    const lanHienTai = lanTimKiem;
    yeuCau?.abort();
    yeuCau = new AbortController();

    const q = oTimKiem.value.trim();
    const url = new URL("api/san-pham.php", window.location.href);
    url.searchParams.set("q", q);
    if (trangThaiFetch) {
      trangThaiFetch.hidden = false;
      trangThaiFetch.textContent = "Đang tìm sản phẩm…";
    }

    try {
      const phanHoi = await fetch(url.toString(), {
        method: "GET",
        headers: { Accept: "application/json" },
        signal: yeuCau.signal,
      });
      if (!phanHoi.ok) throw new Error(`HTTP ${phanHoi.status}`);
      const duLieu = await phanHoi.json();
      if (!duLieu.thanhCong || !Array.isArray(duLieu.duLieu)) {
        throw new Error("Dữ liệu API không đúng định dạng");
      }
      if (lanHienTai !== lanTimKiem) return;
      danhSachDaLoc = apDungBoLoc(duLieu.duLieu);
      trangDangXem = 1;
      hienThiKetQua();
      if (trangThaiFetch) trangThaiFetch.hidden = true;
    } catch (loi) {
      if (loi.name === "AbortError" || lanHienTai !== lanTimKiem) return;
      console.error("Lỗi tìm kiếm Fetch API:", loi);
      if (trangThaiFetch) {
        trangThaiFetch.hidden = false;
        trangThaiFetch.textContent = "Không tải được kết quả trực tiếp. Nhấn Tìm kiếm để thử cách thông thường.";
      }
    }
  }

  function lenLichTimKiem() {
    clearTimeout(boHenGio);
    boHenGio = setTimeout(timKiemTrucTiep, 350);
  }

  oTimKiem.addEventListener("input", lenLichTimKiem);
  // Giữ các bộ lọc hoạt động khi đang dùng tìm kiếm trực tiếp.
  oDanhMuc?.addEventListener("change", lenLichTimKiem);
  oGiaMax?.addEventListener("change", lenLichTimKiem);
  oSapXep?.addEventListener("change", lenLichTimKiem);
});
