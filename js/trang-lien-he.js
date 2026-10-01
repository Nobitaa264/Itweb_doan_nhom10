/*
  trang-lien-he.js - Xử lý biểu mẫu liên hệ/đặt hàng StyleHub.
  Form dùng Constraint Validation API, setCustomValidity và kiểm tra khi blur/submit.
  Dữ liệu hợp lệ được gửi bằng Fetch API POST và khóa nút gửi trong lúc chờ phản hồi.
  Cách thử: bỏ trống từng trường, rời khỏi trường, sau đó nhập hợp lệ và gửi biểu mẫu.
*/

const bieuMau =
  document.querySelector("#form-dat-hang");

const trangThaiGui =
  document.querySelector("#trang-thai-gui");

const nutGui =
  document.querySelector("#nut-gui-dat-hang");

const hoTen =
  document.querySelector("#ho-ten");

const email =
  document.querySelector("#email");

const soDienThoai =
  document.querySelector("#so-dien-thoai");

const sanPham =
  document.querySelector("#san-pham");

const soLuong =
  document.querySelector("#so-luong");

const ghiChu =
  document.querySelector("#ghi-chu");

const loiHoTen =
  document.querySelector("#loi-ho-ten");

const loiEmail =
  document.querySelector("#loi-email");

const loiSoDienThoai =
  document.querySelector("#loi-so-dien-thoai");

const loiSanPham =
  document.querySelector("#loi-san-pham");

const loiSoLuong =
  document.querySelector("#loi-so-luong");


function hienThiLoi(
  phanTuNhap,
  phanTuLoi,
  noiDung
) {
  if (
    phanTuNhap === null ||
    phanTuLoi === null
  ) {
    return;
  }

  phanTuLoi.textContent =
    noiDung;

  phanTuNhap.classList.add(
    "truong-loi"
  );

  phanTuNhap.setAttribute(
    "aria-invalid",
    "true"
  );
}


function xoaLoi(
  phanTuNhap,
  phanTuLoi
) {
  if (
    phanTuNhap === null ||
    phanTuLoi === null
  ) {
    return;
  }

  phanTuLoi.textContent =
    "";

  phanTuNhap.classList.remove(
    "truong-loi"
  );

  phanTuNhap.setAttribute(
    "aria-invalid",
    "false"
  );
}


function datLoiTuyChinh(
  phanTuNhap,
  noiDung
) {
  if (phanTuNhap === null) {
    return;
  }

  phanTuNhap.setCustomValidity(
    noiDung
  );
}


function kiemTraHoTen() {
  if (
    hoTen === null ||
    loiHoTen === null
  ) {
    return false;
  }

  datLoiTuyChinh(
    hoTen,
    ""
  );

  const giaTri =
    hoTen.value.trim();

  if (giaTri === "") {
    datLoiTuyChinh(
      hoTen,
      "Vui lòng nhập họ và tên."
    );
  } else if (giaTri.length < 2) {
    datLoiTuyChinh(
      hoTen,
      "Họ và tên phải có ít nhất 2 ký tự."
    );
  }

  if (!hoTen.validity.valid) {
    hienThiLoi(
      hoTen,
      loiHoTen,
      hoTen.validationMessage
    );

    return false;
  }

  xoaLoi(
    hoTen,
    loiHoTen
  );

  return true;
}


function kiemTraEmail() {
  if (
    email === null ||
    loiEmail === null
  ) {
    return false;
  }

  datLoiTuyChinh(
    email,
    ""
  );

  const giaTri =
    email.value.trim();

  if (giaTri === "") {
    datLoiTuyChinh(
      email,
      "Vui lòng nhập địa chỉ email."
    );
  } else if (
    email.validity.typeMismatch
  ) {
    datLoiTuyChinh(
      email,
      "Vui lòng nhập địa chỉ email hợp lệ."
    );
  }

  if (!email.validity.valid) {
    hienThiLoi(
      email,
      loiEmail,
      email.validationMessage
    );

    return false;
  }

  xoaLoi(
    email,
    loiEmail
  );

  return true;
}


function kiemTraSoDienThoai() {
  if (
    soDienThoai === null ||
    loiSoDienThoai === null
  ) {
    return false;
  }

  datLoiTuyChinh(
    soDienThoai,
    ""
  );

  const giaTri =
    soDienThoai.value.trim();

  if (giaTri === "") {
    datLoiTuyChinh(
      soDienThoai,
      "Vui lòng nhập số điện thoại."
    );
  } else if (
    soDienThoai.validity.patternMismatch
  ) {
    datLoiTuyChinh(
      soDienThoai,
      "Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0."
    );
  }

  if (
    !soDienThoai.validity.valid
  ) {
    hienThiLoi(
      soDienThoai,
      loiSoDienThoai,
      soDienThoai.validationMessage
    );

    return false;
  }

  xoaLoi(
    soDienThoai,
    loiSoDienThoai
  );

  return true;
}


function kiemTraSanPham() {
  if (
    sanPham === null ||
    loiSanPham === null
  ) {
    return false;
  }

  datLoiTuyChinh(
    sanPham,
    ""
  );

  if (sanPham.value === "") {
    datLoiTuyChinh(
      sanPham,
      "Vui lòng chọn sản phẩm."
    );
  }

  if (!sanPham.validity.valid) {
    hienThiLoi(
      sanPham,
      loiSanPham,
      sanPham.validationMessage
    );

    return false;
  }

  xoaLoi(
    sanPham,
    loiSanPham
  );

  return true;
}


function kiemTraSoLuong() {
  if (
    soLuong === null ||
    loiSoLuong === null
  ) {
    return false;
  }

  datLoiTuyChinh(
    soLuong,
    ""
  );

  const giaTri =
    Number(soLuong.value);

  if (soLuong.value === "") {
    datLoiTuyChinh(
      soLuong,
      "Vui lòng nhập số lượng."
    );
  } else if (
    soLuong.validity.badInput ||
    !Number.isInteger(giaTri) ||
    giaTri < 1 ||
    giaTri > 99
  ) {
    datLoiTuyChinh(
      soLuong,
      "Số lượng phải là số nguyên từ 1 đến 99."
    );
  }

  if (!soLuong.validity.valid) {
    hienThiLoi(
      soLuong,
      loiSoLuong,
      soLuong.validationMessage
    );

    return false;
  }

  xoaLoi(
    soLuong,
    loiSoLuong
  );

  return true;
}


function kiemTraTatCa() {
  const ketQuaHoTen =
    kiemTraHoTen();

  const ketQuaEmail =
    kiemTraEmail();

  const ketQuaSoDienThoai =
    kiemTraSoDienThoai();

  const ketQuaSanPham =
    kiemTraSanPham();

  const ketQuaSoLuong =
    kiemTraSoLuong();

  return (
    ketQuaHoTen &&
    ketQuaEmail &&
    ketQuaSoDienThoai &&
    ketQuaSanPham &&
    ketQuaSoLuong
  );
}


function xoaTatCaLoi() {
  const cacCap =
    [
      [hoTen, loiHoTen],
      [email, loiEmail],
      [soDienThoai, loiSoDienThoai],
      [sanPham, loiSanPham],
      [soLuong, loiSoLuong]
    ];

  cacCap.forEach(
    ([phanTuNhap, phanTuLoi]) => {
      if (phanTuNhap !== null) {
        phanTuNhap.setCustomValidity(
          ""
        );
      }

      xoaLoi(
        phanTuNhap,
        phanTuLoi
      );
    }
  );
}


function taoDuLieuGui() {
  const phuongThucLienHe =
    document.querySelector(
      'input[name="phuongThucLienHe"]:checked'
    );

  const dongY =
    document.querySelector("#dong-y");

  return {
    hoTen:
      hoTen !== null
        ? hoTen.value.trim()
        : "",

    email:
      email !== null
        ? email.value.trim()
        : "",

    soDienThoai:
      soDienThoai !== null
        ? soDienThoai.value.trim()
        : "",

    sanPham:
      sanPham !== null
        ? sanPham.value
        : "",

    soLuong:
      soLuong !== null
        ? Number(soLuong.value)
        : 0,

    ghiChu:
      ghiChu !== null
        ? ghiChu.value.trim()
        : "",

    phuongThucLienHe:
      phuongThucLienHe !== null
        ? phuongThucLienHe.value
        : "",

    dongY:
      dongY !== null
        ? dongY.checked
        : false
  };
}


function hienThiTrangThai(
  noiDung
) {
  if (trangThaiGui === null) {
    return;
  }

  trangThaiGui.textContent =
    noiDung;
}


function datTrangThaiNutGui(
  dangGui
) {
  if (nutGui === null) {
    return;
  }

  nutGui.disabled =
    dangGui;

  if (dangGui) {
    nutGui.textContent =
      "Đang gửi...";
  } else {
    nutGui.textContent =
      "Gửi thông tin đặt hàng";
  }
}


async function guiDonHang(
  duLieu
) {
  const response =
    await fetch(
      "https://jsonplaceholder.typicode.com/posts",
      {
        method: "POST",

        headers: {
          "Content-Type":
            "application/json"
        },

        body:
          JSON.stringify(
            duLieu
          )
      }
    );

  if (!response.ok) {
    throw new Error(
      `Gửi dữ liệu thất bại. Mã lỗi: ${response.status}`
    );
  }

  return response.json();
}


function timTruongLoiDauTien() {
  const cacTruong =
    [
      hoTen,
      email,
      soDienThoai,
      sanPham,
      soLuong
    ];

  return cacTruong.find(
    (truong) =>
      truong !== null &&
      !truong.validity.valid
  );
}


async function xuLyGuiBieuMau(
  event
) {
  event.preventDefault();

  hienThiTrangThai(
    ""
  );

  const hopLe =
    kiemTraTatCa();

  if (!hopLe) {
    hienThiTrangThai(
      "Vui lòng kiểm tra lại các thông tin chưa hợp lệ."
    );

    const truongLoiDauTien =
      timTruongLoiDauTien();

    if (
      truongLoiDauTien !== undefined
    ) {
      truongLoiDauTien.focus();
    }

    return;
  }

  const duLieu =
    taoDuLieuGui();

  hienThiTrangThai(
    "Đang gửi thông tin đặt hàng..."
  );

  datTrangThaiNutGui(
    true
  );

  try {
    const ketQua =
      await guiDonHang(
        duLieu
      );

    hienThiTrangThai(
      `Gửi thông tin thành công. Mã phản hồi: ${ketQua.id}.`
    );

    if (bieuMau !== null) {
      bieuMau.reset();
    }

    xoaTatCaLoi();

  } catch (error) {
    hienThiTrangThai(
      "Không thể gửi thông tin lúc này. Vui lòng thử lại."
    );

  } finally {
    datTrangThaiNutGui(
      false
    );
  }
}


if (
  hoTen !== null
) {
  hoTen.addEventListener(
    "blur",
    kiemTraHoTen
  );
}


if (
  email !== null
) {
  email.addEventListener(
    "blur",
    kiemTraEmail
  );
}


if (
  soDienThoai !== null
) {
  soDienThoai.addEventListener(
    "blur",
    kiemTraSoDienThoai
  );
}


if (
  sanPham !== null
) {
  sanPham.addEventListener(
    "blur",
    kiemTraSanPham
  );

  sanPham.addEventListener(
    "change",
    kiemTraSanPham
  );
}


if (
  soLuong !== null
) {
  soLuong.addEventListener(
    "blur",
    kiemTraSoLuong
  );

  soLuong.addEventListener(
    "change",
    kiemTraSoLuong
  );
}


if (
  bieuMau !== null
) {
  bieuMau.addEventListener(
    "submit",
    xuLyGuiBieuMau
  );
}