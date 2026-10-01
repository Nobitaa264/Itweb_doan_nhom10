/*
  api.js - Chứa hàm dùng chung để tải dữ liệu JSON cho website StyleHub.
  Hàm taiJSON sử dụng Fetch API, async/await và kiểm tra response.ok.
  Nếu máy chủ trả về lỗi, hàm sẽ phát sinh Error để trang gọi xử lý trạng thái lỗi.
  Cách thử: truyền URL JSON hợp lệ và URL sai để kiểm tra hai trường hợp.
*/

async function taiJSON(url) {
  const response =
    await fetch(url);

  if (!response.ok) {
    throw new Error(
      `Không thể tải dữ liệu. Mã lỗi: ${response.status}`
    );
  }

  const duLieu =
    await response.json();

  return duLieu;
}

export {
  taiJSON
};