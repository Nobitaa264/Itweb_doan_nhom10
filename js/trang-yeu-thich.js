/* TH7 - trang yeu thich: tach JavaScript noi tuyen de tuan thu CSP.
 * Du lieu yeu thich van duoc luu trong localStorage voi khoa stylehubYeuThich.
 */
document.addEventListener("DOMContentLoaded", () => {
    const isYeuThichPage = new URLSearchParams(window.location.search).get("yeuthich") === "1";
    if (!isYeuThichPage) return;

    const luoiSanPham = document.getElementById("danh-sach-san-pham");
    const thongBaoRong = document.getElementById("thong-bao-rong-yeu-thich");
    const soLuongText = document.getElementById("so-luong-ket-qua");
    if (!luoiSanPham) return;

    const filterYeuThich = () => {
        const rawData = localStorage.getItem("stylehubYeuThich");
        let dsYeuThich = [];
        try {
            const parsed = JSON.parse(rawData);
            dsYeuThich = Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            dsYeuThich = [];
        }

        const cacTheSanPham = luoiSanPham.querySelectorAll(".the-san-pham[data-id-san-pham]");
        let count = 0;

        cacTheSanPham.forEach((the) => {
            const id = the.getAttribute("data-id-san-pham");
            const duocYeuThich = dsYeuThich.includes(id);
            the.hidden = !duocYeuThich;
            if (duocYeuThich) count++;
        });

        if (soLuongText) {
            soLuongText.textContent = `${count} sản phẩm`;
        }

        if (thongBaoRong) {
            thongBaoRong.hidden = count !== 0;
        }
    };

    filterYeuThich();
    window.addEventListener("stylehub:yeu-thich-thay-doi", filterYeuThich);
    window.addEventListener("storage", filterYeuThich);
});
