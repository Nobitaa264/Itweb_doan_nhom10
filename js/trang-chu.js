/*
  trang-chu.js - Tải nội dung động cho trang chủ StyleHub.
  Dữ liệu được lấy từ API công khai bằng hàm taiJSON dùng chung trong api.js.
  Trang hiển thị trạng thái đang tải, thành công hoặc lỗi và không dùng innerHTML.
  Video YouTube chỉ được tạo khi người dùng bấm nút để hạn chế tài nguyên bên thứ ba.
*/

import {
  taiJSON
} from "./api.js";


const trangThaiTin =
  document.querySelector(
    "#trang-thai-tin"
  );

const danhSachTin =
  document.querySelector(
    "#danh-sach-tin"
  );

const nutXemVideo =
  document.querySelector(
    "#nut-xem-video"
  );

const khuVucVideo =
  document.querySelector(
    "#khu-vuc-video"
  );


function hienThiTrangThaiTin(
  noiDung
) {
  if (trangThaiTin === null) {
    return;
  }

  trangThaiTin.textContent =
    noiDung;
}


function xoaDanhSachTin() {
  if (danhSachTin === null) {
    return;
  }

  danhSachTin.replaceChildren();
}


function taoTheTin(baiViet) {
  const article =
    document.createElement(
      "article"
    );

  article.classList.add(
    "the-tin"
  );


  const tieuDe =
    document.createElement(
      "h3"
    );

  tieuDe.textContent =
    baiViet.title;


  const noiDung =
    document.createElement(
      "p"
    );

  noiDung.textContent =
    baiViet.body;


  article.append(
    tieuDe,
    noiDung
  );

  return article;
}


function hienThiDanhSachTin(
  danhSach
) {
  if (danhSachTin === null) {
    return;
  }

  xoaDanhSachTin();

  if (
    !Array.isArray(danhSach) ||
    danhSach.length === 0
  ) {
    hienThiTrangThaiTin(
      "Hiện chưa có nội dung mới."
    );

    return;
  }

  const fragment =
    document.createDocumentFragment();

  danhSach.forEach(
    (baiViet) => {
      fragment.append(
        taoTheTin(baiViet)
      );
    }
  );

  danhSachTin.append(
    fragment
  );

  hienThiTrangThaiTin(
    `Đã tải ${danhSach.length} nội dung mới.`
  );
}


async function taiTinMoi() {
  if (
    trangThaiTin === null ||
    danhSachTin === null
  ) {
    return;
  }

  hienThiTrangThaiTin(
    "Đang tải nội dung mới..."
  );

  xoaDanhSachTin();

  try {
    const duLieu = await taiJSON(
  "https://jsonplaceholder.typicode.com/posts?_limit=3"
);

    hienThiDanhSachTin(
      duLieu
    );

  } catch (error) {
    hienThiTrangThaiTin(
      "Không thể tải nội dung mới. Vui lòng thử lại sau."
    );

    console.error(
      "Lỗi khi tải nội dung trang chủ:",
      error
    );
  }
}


function taoVideoGioiThieu() {
  if (
    nutXemVideo === null ||
    khuVucVideo === null
  ) {
    return;
  }

  const iframeDaCo =
    khuVucVideo.querySelector(
      "iframe"
    );

  if (iframeDaCo !== null) {
    iframeDaCo.focus();
    return;
  }


  const iframe =
    document.createElement(
      "iframe"
    );

  iframe.title =
    "Video giới thiệu thời trang StyleHub";

  iframe.src =
    "https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ";

  iframe.width =
    "560";

  iframe.height =
    "315";

  iframe.loading =
    "lazy";

  iframe.allow =
    "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";

  iframe.allowFullscreen =
    true;

  iframe.setAttribute(
    "tabindex",
    "0"
  );


  khuVucVideo.append(
    iframe
  );


  nutXemVideo.disabled =
    true;

  nutXemVideo.textContent =
    "Video đã được tải";


  iframe.focus();
}


if (
  nutXemVideo !== null
) {
  nutXemVideo.addEventListener(
    "click",
    taoVideoGioiThieu
  );
}


taiTinMoi();