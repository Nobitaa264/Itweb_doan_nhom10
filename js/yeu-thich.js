/*
  yeu-thich.js - Quản lý sản phẩm yêu thích của StyleHub.
  Mã sản phẩm yêu thích được lưu trong localStorage và đồng bộ giữa các trang.
  Sự kiện nút yêu thích được xử lý bằng event delegation tại document.
  Cách thử: yêu thích sản phẩm, kiểm tra bộ đếm và tải lại trang để kiểm tra lưu trạng thái.
*/

const khoaLuuYeuThich = "stylehubYeuThich";

function layDanhSachYeuThich() {
  const duLieuDaLuu = localStorage.getItem(khoaLuuYeuThich);

  if (duLieuDaLuu === null) {
    return [];
  }

  try {
    const danhSach = JSON.parse(duLieuDaLuu);

    if (!Array.isArray(danhSach)) {
      return [];
    }

    return danhSach;
  } catch (error) {
    console.error("Không thể đọc danh sách yêu thích:", error);

    return [];
  }
}

function luuDanhSachYeuThich(danhSach) {
  localStorage.setItem(khoaLuuYeuThich, JSON.stringify(danhSach));
}

function laSanPhamYeuThich(idSanPham) {
  const danhSach = layDanhSachYeuThich();

  return danhSach.includes(idSanPham);
}

function themYeuThich(idSanPham) {
  const danhSach = layDanhSachYeuThich();

  if (!danhSach.includes(idSanPham)) {
    danhSach.push(idSanPham);

    luuDanhSachYeuThich(danhSach);
  }
}

function boYeuThich(idSanPham) {
  const danhSach = layDanhSachYeuThich();

  const danhSachMoi = danhSach.filter((id) => id !== idSanPham);

  luuDanhSachYeuThich(danhSachMoi);
}

function demSanPhamYeuThich() {
  return layDanhSachYeuThich().length;
}

function capNhatBoDemYeuThich() {
  const cacBoDem = document.querySelectorAll("[data-bo-dem-yeu-thich]");

  const soLuong = demSanPhamYeuThich();

  cacBoDem.forEach((boDem) => {
    boDem.textContent = String(soLuong);

    boDem.setAttribute("aria-label", `${soLuong} sản phẩm yêu thích`);
  });
}

function capNhatNutYeuThich(nutYeuThich, idSanPham) {
  const dangYeuThich = laSanPhamYeuThich(idSanPham);

  nutYeuThich.setAttribute("aria-pressed", String(dangYeuThich));

  nutYeuThich.classList.toggle("nut-yeu-thich--da-chon", dangYeuThich);

  if (dangYeuThich) {
    nutYeuThich.textContent = "♥ Đã yêu thích";
  } else {
    nutYeuThich.textContent = "♡ Yêu thích";
  }
}

function capNhatTatCaNutYeuThich() {
  const cacNut = document.querySelectorAll(".nut-yeu-thich[data-id-san-pham]");

  cacNut.forEach((nut) => {
    const idSanPham = nut.dataset.idSanPham;

    if (idSanPham) {
      capNhatNutYeuThich(nut, idSanPham);
    }
  });
}

function xuLyYeuThich(nutYeuThich, idSanPham) {
  if (laSanPhamYeuThich(idSanPham)) {
    boYeuThich(idSanPham);
  } else {
    themYeuThich(idSanPham);
  }

  capNhatTatCaNutYeuThich();
  capNhatBoDemYeuThich();

  window.dispatchEvent(new CustomEvent("stylehub:yeu-thich-thay-doi"));
}

function taoNutYeuThich(idSanPham) {
  const nut = document.createElement("button");

  nut.type = "button";

  nut.classList.add("nut", "nut-yeu-thich");

  nut.dataset.idSanPham = idSanPham;

  capNhatNutYeuThich(nut, idSanPham);

  return nut;
}

function xuLySuKienYeuThich(event) {
  const phanTuDuocBam = event.target;

  if (!(phanTuDuocBam instanceof Element)) {
    return;
  }

  const nutYeuThich = phanTuDuocBam.closest(".nut-yeu-thich[data-id-san-pham]");

  if (nutYeuThich === null) {
    return;
  }

  const idSanPham = nutYeuThich.dataset.idSanPham;

  if (!idSanPham) {
    return;
  }

  xuLyYeuThich(nutYeuThich, idSanPham);
}

document.addEventListener("click", xuLySuKienYeuThich);

window.addEventListener("storage", () => {
  capNhatTatCaNutYeuThich();
  capNhatBoDemYeuThich();
});

window.addEventListener("stylehub:yeu-thich-thay-doi", () => {
  capNhatTatCaNutYeuThich();
  capNhatBoDemYeuThich();
});

capNhatBoDemYeuThich();

export {
  layDanhSachYeuThich,
  laSanPhamYeuThich,
  taoNutYeuThich,
  demSanPhamYeuThich,
  capNhatBoDemYeuThich,
};
