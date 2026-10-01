/*
  trang-danh-sach.js - Xử lý trang danh sách sản phẩm StyleHub.
  Tải JSON, tìm kiếm, lọc, sắp xếp và tích hợp sản phẩm yêu thích.
  Nội dung động được tạo bằng DOM API, không dùng innerHTML.
*/

import { taiJSON } from "./api.js";
import { taoNutYeuThich, layDanhSachYeuThich } from "./yeu-thich.js";

const duongDanDuLieu = "data/san-pham.json";
const oTimKiem = document.querySelector("#tim-kiem");
const boLocDanhMuc = document.querySelector("#loc-danh-muc");
const sapXep = document.querySelector("#sap-xep");
const danhSachSanPham = document.querySelector("#danh-sach-san-pham");
const trangThaiDanhSach = document.querySelector("#trang-thai-danh-sach");
const soLuongKetQua = document.querySelector("#so-luong-ket-qua");
const tieuDeTrang = document.querySelector(".tieu-de-trang");

let tatCaSanPham = [];

function chuanHoaVanBan(vanBan) {
  return String(vanBan)
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/đ/g, "d")
    .replace(/Đ/g, "D")
    .toLowerCase()
    .trim();
}

function dinhDangGia(gia) {
  return `${Number(gia).toLocaleString("vi-VN")}đ`;
}

function xoaDanhSachHienTai() {
  if (danhSachSanPham === null) return;
  while (danhSachSanPham.firstChild !== null) {
    danhSachSanPham.removeChild(danhSachSanPham.firstChild);
  }
}

function hienThiTrangThai(noiDung) {
  if (trangThaiDanhSach === null) return;
  trangThaiDanhSach.textContent = noiDung;
}

function taoTheSanPham(sanPham) {
  const article = document.createElement("article");
  article.classList.add("the-san-pham");

  const hinhAnh = document.createElement("img");
  hinhAnh.classList.add("the-san-pham__anh");
  hinhAnh.src = sanPham.hinhAnh;
  hinhAnh.alt = sanPham.ten;
  hinhAnh.width = 300;
  hinhAnh.height = 300;
  hinhAnh.loading = "lazy";

  const noiDung = document.createElement("div");
  noiDung.classList.add("the-san-pham__noi-dung");

  const maSanPham = document.createElement("p");
  maSanPham.classList.add("ma-san-pham");
  maSanPham.textContent = sanPham.id;

  const tenSanPham = document.createElement("h3");
  tenSanPham.textContent = sanPham.ten;

  const moTa = document.createElement("p");
  moTa.textContent = sanPham.moTa;

  const danhMuc = document.createElement("p");
  const nhanDanhMuc = document.createElement("strong");
  nhanDanhMuc.textContent = "Danh mục: ";
  const tenDanhMuc = document.createTextNode(sanPham.danhMuc);
  danhMuc.append(nhanDanhMuc, tenDanhMuc);

  const gia = document.createElement("p");
  gia.classList.add("gia");
  gia.textContent = dinhDangGia(sanPham.gia);

  const tinhTrang = document.createElement("p");
  tinhTrang.classList.add("tinh-trang");
  tinhTrang.textContent = sanPham.tinhTrang;

  const nhomHanhDong = document.createElement("div");
  nhomHanhDong.classList.add("nhom-hanh-dong-san-pham");

  const lienKetChiTiet = document.createElement("a");
  lienKetChiTiet.classList.add("nut");
  lienKetChiTiet.href = `chi-tiet.html?id=${encodeURIComponent(sanPham.id)}`;
  lienKetChiTiet.textContent = "Xem chi tiết";

  const nutYeuThich = taoNutYeuThich(sanPham.id);

  nhomHanhDong.append(lienKetChiTiet, nutYeuThich);
  noiDung.append(
    maSanPham,
    tenSanPham,
    moTa,
    danhMuc,
    gia,
    tinhTrang,
    nhomHanhDong,
  );
  article.append(hinhAnh, noiDung);

  return article;
}

function hienThiSanPham(dsSanPham) {
  if (danhSachSanPham === null) return;
  xoaDanhSachHienTai();

  if (soLuongKetQua !== null) {
    soLuongKetQua.textContent = `${dsSanPham.length} sản phẩm`;
  }

  if (dsSanPham.length === 0) {
    hienThiTrangThai("Không tìm thấy sản phẩm phù hợp.");
    return;
  }

  hienThiTrangThai("");

  const fragment = document.createDocumentFragment();
  dsSanPham.forEach((sanPham) => {
    const theSanPham = taoTheSanPham(sanPham);
    fragment.append(theSanPham);
  });

  danhSachSanPham.append(fragment);
}

function capNhatDanhSach() {
  const tuKhoa = oTimKiem !== null ? chuanHoaVanBan(oTimKiem.value) : "";
  const danhMucDangChon = boLocDanhMuc !== null ? boLocDanhMuc.value : "tat-ca";
  const kieuSapXep = sapXep !== null ? sapXep.value : "mac-dinh";

  // Kiểm tra tham số URL để biết có đang ở trang Yêu thích không
  const thamSoUrl = new URLSearchParams(window.location.search);
  const cheDoYeuThich = thamSoUrl.get("yeuthich") === "1";
  const danhSachIdYeuThich = layDanhSachYeuThich();

  // Đổi tiêu đề cho phù hợp
  if (cheDoYeuThich && tieuDeTrang !== null) {
    tieuDeTrang.textContent = "Sản phẩm yêu thích";
    document.title = "Sản phẩm yêu thích - StyleHub";
  } else if (tieuDeTrang !== null) {
    tieuDeTrang.textContent = "Danh sách sản phẩm";
    document.title = "Danh sách sản phẩm - StyleHub";
  }

  let ketQua = tatCaSanPham.filter((sanPham) => {
    // Lọc riêng mục Yêu thích
    if (cheDoYeuThich && !danhSachIdYeuThich.includes(sanPham.id)) {
      return false;
    }

    const tenDaChuanHoa = chuanHoaVanBan(sanPham.ten);
    const maDaChuanHoa = chuanHoaVanBan(sanPham.id);
    const danhMucDaChuanHoa = chuanHoaVanBan(sanPham.danhMuc);

    const khopTuKhoa =
      tenDaChuanHoa.includes(tuKhoa) ||
      maDaChuanHoa.includes(tuKhoa) ||
      danhMucDaChuanHoa.includes(tuKhoa);
    const khopDanhMuc =
      danhMucDangChon === "tat-ca" || sanPham.danhMuc === danhMucDangChon;

    return khopTuKhoa && khopDanhMuc;
  });

  ketQua = [...ketQua];

  if (kieuSapXep === "gia-tang") ketQua.sort((a, b) => a.gia - b.gia);
  if (kieuSapXep === "gia-giam") ketQua.sort((a, b) => b.gia - a.gia);
  if (kieuSapXep === "ten-a-z")
    ketQua.sort((a, b) => a.ten.localeCompare(b.ten, "vi"));
  if (kieuSapXep === "ten-z-a")
    ketQua.sort((a, b) => b.ten.localeCompare(a.ten, "vi"));

  hienThiSanPham(ketQua);
}

async function taiDuLieuSanPham() {
  hienThiTrangThai("Đang tải sản phẩm...");
  try {
    const duLieu = await taiJSON(duongDanDuLieu);
    if (!Array.isArray(duLieu)) {
      throw new Error("Dữ liệu sản phẩm không đúng định dạng.");
    }
    tatCaSanPham = duLieu;
    capNhatDanhSach();
  } catch (error) {
    tatCaSanPham = [];
    xoaDanhSachHienTai();
    if (soLuongKetQua !== null) {
      soLuongKetQua.textContent = "0 sản phẩm";
    }
    hienThiTrangThai("Không thể tải danh sách sản phẩm. Vui lòng thử lại.");
    console.error("Lỗi khi tải dữ liệu sản phẩm:", error);
  }
}

// Lắng nghe sự kiện người dùng
if (oTimKiem !== null) oTimKiem.addEventListener("input", capNhatDanhSach);
if (boLocDanhMuc !== null)
  boLocDanhMuc.addEventListener("change", capNhatDanhSach);
if (sapXep !== null) sapXep.addEventListener("change", capNhatDanhSach);

// Tự động ẩn sản phẩm ngay khi bỏ Yêu thích
window.addEventListener("stylehub:yeu-thich-thay-doi", capNhatDanhSach);

// Khởi chạy
taiDuLieuSanPham();
