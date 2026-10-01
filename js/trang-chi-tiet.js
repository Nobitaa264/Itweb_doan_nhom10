/*
  trang-chi-tiet.js - Xử lý trang chi tiết sản phẩm StyleHub.
  Lấy id từ URL, tải JSON, tìm sản phẩm và tích hợp chức năng yêu thích.
  Nội dung động được tạo bằng DOM API, không dùng innerHTML.
  Cách thử: mở chi-tiet.html?id=SH001, bấm Yêu thích và kiểm tra khi tải lại trang.
*/

import {
  taiJSON
} from "./api.js";

import {
  taoNutYeuThich
} from "./yeu-thich.js";


const duongDanDuLieu =
  "data/san-pham.json";

const noiDungChiTiet =
  document.querySelector("#noi-dung-chi-tiet");

const trangThaiChiTiet =
  document.querySelector("#trang-thai-chi-tiet");


function dinhDangGia(gia) {
  return `${Number(gia).toLocaleString("vi-VN")}đ`;
}


function hienThiTrangThai(noiDung) {
  if (trangThaiChiTiet === null) {
    return;
  }

  trangThaiChiTiet.textContent =
    noiDung;
}


function xoaNoiDungChiTiet() {
  if (noiDungChiTiet === null) {
    return;
  }

  while (
    noiDungChiTiet.firstChild !== null
  ) {
    noiDungChiTiet.removeChild(
      noiDungChiTiet.firstChild
    );
  }
}


function taoDongThongSo(
  tenThongSo,
  giaTri
) {
  const dong =
    document.createElement("tr");

  const tieuDe =
    document.createElement("th");

  tieuDe.scope = "row";

  tieuDe.textContent =
    tenThongSo;

  const noiDung =
    document.createElement("td");

  noiDung.textContent =
    giaTri;

  dong.append(
    tieuDe,
    noiDung
  );

  return dong;
}


function taoChiTietSanPham(sanPham) {
  const article =
    document.createElement("article");

  article.classList.add(
    "chi-tiet-san-pham"
  );


  const phanChinh =
    document.createElement("div");

  phanChinh.classList.add(
    "chi-tiet-san-pham__chinh"
  );


  const tenSanPham =
    document.createElement("h2");

  tenSanPham.textContent =
    sanPham.ten;


  const figure =
    document.createElement("figure");

  figure.classList.add(
    "hinh-san-pham"
  );


  const hinhAnh =
    document.createElement("img");

  hinhAnh.src =
    sanPham.hinhAnh;

  hinhAnh.alt =
    sanPham.ten;

  hinhAnh.width = 400;
  hinhAnh.height = 400;


  const chuThich =
    document.createElement("figcaption");

  chuThich.textContent =
    `${sanPham.ten} - ${sanPham.danhMuc}.`;


  figure.append(
    hinhAnh,
    chuThich
  );


  const khoiMoTa =
    document.createElement("section");

  khoiMoTa.classList.add(
    "khoi-noi-dung"
  );


  const tieuDeMoTa =
    document.createElement("h3");

  tieuDeMoTa.textContent =
    "Mô tả sản phẩm";


  const moTa =
    document.createElement("p");

  moTa.textContent =
    sanPham.moTa;


  const nutYeuThich =
    taoNutYeuThich(
      sanPham.id
    );


  khoiMoTa.append(
    tieuDeMoTa,
    moTa,
    nutYeuThich
  );


  phanChinh.append(
    tenSanPham,
    figure,
    khoiMoTa
  );


  const phanPhu =
    document.createElement("aside");

  phanPhu.classList.add(
    "chi-tiet-san-pham__phu"
  );


  const khoiThongSo =
    document.createElement("section");

  khoiThongSo.classList.add(
    "khoi-noi-dung"
  );


  const tieuDeThongSo =
    document.createElement("h3");

  tieuDeThongSo.textContent =
    "Thông số sản phẩm";


  const bangCuon =
    document.createElement("div");

  bangCuon.classList.add(
    "bang-cuon"
  );


  const bang =
    document.createElement("table");

  bang.classList.add(
    "bang-chi-tiet"
  );


  const caption =
    document.createElement("caption");

  caption.textContent =
    `Thông tin chi tiết ${sanPham.ten}`;


  const tbody =
    document.createElement("tbody");


  tbody.append(
    taoDongThongSo(
      "Mã sản phẩm",
      sanPham.id
    ),

    taoDongThongSo(
      "Danh mục",
      sanPham.danhMuc
    ),

    taoDongThongSo(
      "Giá",
      dinhDangGia(
        sanPham.gia
      )
    ),

    taoDongThongSo(
      "Chất liệu",
      sanPham.chatLieu
    ),

    taoDongThongSo(
      "Màu sắc",
      sanPham.mauSac
    ),

    taoDongThongSo(
      "Kích cỡ",
      sanPham.kichCo
    ),

    taoDongThongSo(
      "Tình trạng",
      sanPham.tinhTrang
    )
  );


  bang.append(
    caption,
    tbody
  );

  bangCuon.append(
    bang
  );


  khoiThongSo.append(
    tieuDeThongSo,
    bangCuon
  );


  phanPhu.append(
    khoiThongSo
  );


  article.append(
    phanChinh,
    phanPhu
  );


  return article;
}


function hienThiChiTiet(sanPham) {
  if (noiDungChiTiet === null) {
    return;
  }

  xoaNoiDungChiTiet();


  const chiTiet =
    taoChiTietSanPham(
      sanPham
    );


  noiDungChiTiet.append(
    chiTiet
  );


  hienThiTrangThai("");


  document.title =
    `${sanPham.ten} - StyleHub`;
}


function hienThiKhongTimThay() {
  xoaNoiDungChiTiet();


  hienThiTrangThai(
    "Không tìm thấy sản phẩm. Vui lòng quay lại danh sách sản phẩm."
  );


  document.title =
    "Không tìm thấy sản phẩm - StyleHub";
}


async function taiChiTietSanPham() {
  hienThiTrangThai(
    "Đang tải thông tin sản phẩm..."
  );


  const thamSoUrl =
    new URLSearchParams(
      window.location.search
    );


  const idSanPham =
    thamSoUrl.get("id");


  if (
    idSanPham === null ||
    idSanPham.trim() === ""
  ) {
    hienThiKhongTimThay();

    return;
  }


  try {
    const duLieu =
      await taiJSON(
        duongDanDuLieu
      );


    if (!Array.isArray(duLieu)) {
      throw new Error(
        "Dữ liệu sản phẩm không đúng định dạng."
      );
    }


    const sanPham =
      duLieu.find(
        (item) =>
          item.id === idSanPham
      );


    if (sanPham === undefined) {
      hienThiKhongTimThay();

      return;
    }


    hienThiChiTiet(
      sanPham
    );

  } catch (error) {
    xoaNoiDungChiTiet();


    hienThiTrangThai(
      "Không thể tải thông tin sản phẩm. Vui lòng thử lại."
    );


    document.title =
      "Lỗi tải sản phẩm - StyleHub";


    console.error(
      "Lỗi khi tải chi tiết sản phẩm:",
      error
    );
  }
}


taiChiTietSanPham();