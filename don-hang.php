<?php
declare(strict_types=1);

/**
 * don-hang.php - Danh sách và chi tiết đơn hàng của người dùng.
 * - GET /don-hang.php: danh sách đơn của tài khoản đang đăng nhập.
 * - GET /don-hang.php?id=2: xem đơn #2 nếu đúng chủ đơn.
 * - Không có thao tác thay đổi dữ liệu bằng GET.
 */
require_once __DIR__ . '/inc/config.php';

use App\Config\Database;

$tieuDe = 'Đơn hàng của tôi - StyleHub';
$trang = 'don-hang';
$goc = './';
$cssBoSung = 'css/don-hang-chi-tiet.css';

$nguoiDung = nguoiDungHienTai();
$idNguoiDung = is_array($nguoiDung) ? (int) ($nguoiDung['id'] ?? 0) : 0;

if ($idNguoiDung < 1) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Vui lòng đăng nhập để xem đơn hàng.',
    ];
    redirect('dang-nhap.php');
}

$nhanTrangThai = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao hàng',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy',
];

$pdo = Database::connect();
$coYeuCauChiTiet = array_key_exists('id', $_GET);
$donHang = null;
$cacDongChiTiet = [];
$cacDonHang = [];
$khongTimThay = false;

if ($coYeuCauChiTiet) {
    $idNhap = $_GET['id'];
    // Từ chối id[]=1, id âm, id rỗng và id không phải số nguyên.
    $idDonHang = is_string($idNhap) && ctype_digit($idNhap)
        ? filter_var($idNhap, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
        : false;

    if ($idDonHang === false) {
        $khongTimThay = true;
    } else {
        // Quan trọng: lọc cả id đơn và id người dùng tại máy chủ.
        $stmt = $pdo->prepare(
            'SELECT id, ho_ten_nhan, so_dien_thoai_nhan, dia_chi_nhan,
                    ghi_chu, tong_tien, trang_thai, created_at
               FROM don_hang
              WHERE id = :id AND nguoi_dung_id = :nguoi_dung_id
              LIMIT 1'
        );
        $stmt->execute([
            'id' => $idDonHang,
            'nguoi_dung_id' => $idNguoiDung,
        ]);
        $donHang = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($donHang === false) {
            // 404 thay vì tiết lộ một đơn có tồn tại nhưng thuộc người khác.
            $khongTimThay = true;
        } else {
            $stmtChiTiet = $pdo->prepare(
                'SELECT san_pham_id, ten_san_pham, gia_don_vi,
                        so_luong, thanh_tien
                   FROM chi_tiet_don_hang
                  WHERE don_hang_id = :don_hang_id
                  ORDER BY san_pham_id ASC'
            );
            $stmtChiTiet->execute(['don_hang_id' => $idDonHang]);
            $cacDongChiTiet = $stmtChiTiet->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if ($khongTimThay) {
        http_response_code(404);
    }
} else {
    $stmt = $pdo->prepare(
        'SELECT id, ho_ten_nhan, tong_tien, trang_thai, created_at
           FROM don_hang
          WHERE nguoi_dung_id = :nguoi_dung_id
          ORDER BY id DESC'
    );
    $stmt->execute(['nguoi_dung_id' => $idNguoiDung]);
    $cacDonHang = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/menu.php';
?>
<main class="noi-dung" id="noi-dung">
    <div class="bao dh-trang">
        <?php if (is_array($flash) && !empty($flash['noiDung'])): ?>
            <div class="dh-thong-bao <?= ($flash['loai'] ?? '') === 'thanh-cong' ? 'dh-thanh-cong' : 'dh-loi' ?>"
                 role="status">
                <?= e($flash['noiDung']) ?>
            </div>
        <?php endif; ?>

        <?php if ($khongTimThay): ?>
            <h1 class="tieu-de-trang">Không tìm thấy đơn hàng</h1>
            <p>Đơn hàng không tồn tại hoặc bạn không có quyền xem đơn hàng này.</p>
            <a class="dh-lien-ket" href="don-hang.php">← Quay lại đơn hàng của tôi</a>

        <?php elseif ($coYeuCauChiTiet && is_array($donHang)): ?>
            <p><a class="dh-lien-ket" href="don-hang.php">← Quay lại danh sách đơn hàng</a></p>
            <h1 class="tieu-de-trang">Chi tiết đơn hàng #<?= (int) $donHang['id'] ?></h1>

            <section class="dh-the" aria-labelledby="dh-thong-tin">
                <div class="dh-dau-the">
                    <h2 id="dh-thong-tin">Thông tin đơn hàng</h2>
                    <span class="dh-trang-thai">
                        <?= e($nhanTrangThai[(string) $donHang['trang_thai']] ?? $donHang['trang_thai']) ?>
                    </span>
                </div>
                <dl class="dh-thong-tin">
                    <div><dt>Mã đơn hàng</dt><dd>#<?= (int) $donHang['id'] ?></dd></div>
                    <div><dt>Ngày đặt</dt><dd><?= e($donHang['created_at']) ?></dd></div>
                    <div><dt>Người nhận</dt><dd><?= e($donHang['ho_ten_nhan']) ?></dd></div>
                    <div><dt>Số điện thoại</dt><dd><?= e($donHang['so_dien_thoai_nhan']) ?></dd></div>
                    <div class="dh-rong"><dt>Địa chỉ nhận</dt><dd><?= e($donHang['dia_chi_nhan']) ?></dd></div>
                    <?php if (trim((string) ($donHang['ghi_chu'] ?? '')) !== ''): ?>
                        <div class="dh-rong"><dt>Ghi chú</dt>
                            <dd><?= nl2br(e($donHang['ghi_chu'])) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </section>

            <section class="dh-the" aria-labelledby="dh-san-pham">
                <h2 id="dh-san-pham">Các sản phẩm trong đơn</h2>
                <?php if ($cacDongChiTiet === []): ?>
                    <p>Đơn hàng chưa có dòng chi tiết nào.</p>
                <?php else: ?>
                    <div class="dh-cuon-bang">
                        <table class="dh-bang">
                            <thead>
                                <tr>
                                    <th scope="col">Sản phẩm</th>
                                    <th scope="col">Đơn giá</th>
                                    <th scope="col">Số lượng</th>
                                    <th scope="col">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cacDongChiTiet as $dong): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($dong['ten_san_pham']) ?></strong>
                                            <span class="dh-ma">Mã: <?= e($dong['san_pham_id']) ?></span>
                                        </td>
                                        <td class="dh-so"><?= e(vnd((float) $dong['gia_don_vi'])) ?></td>
                                        <td class="dh-so"><?= (int) $dong['so_luong'] ?></td>
                                        <td class="dh-so"><?= e(vnd((float) $dong['thanh_tien'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <p class="dh-tong"><span>Tổng tiền đơn hàng</span>
                    <strong><?= e(vnd((float) $donHang['tong_tien'])) ?></strong>
                </p>
            </section>

        <?php else: ?>
            <h1 class="tieu-de-trang">Đơn hàng của tôi</h1>
            <?php if ($cacDonHang === []): ?>
                <div class="dh-the">
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <p><a class="dh-lien-ket" href="danh-sach.php">Khám phá sản phẩm</a></p>
                </div>
            <?php else: ?>
                <p class="dh-mo-ta">Bạn có <?= count($cacDonHang) ?> đơn hàng. Nhấn “Xem chi tiết” để xem các sản phẩm đã đặt.</p>
                <div class="dh-danh-sach">
                    <?php foreach ($cacDonHang as $muc): ?>
                        <section class="dh-the" aria-labelledby="dh-don-<?= (int) $muc['id'] ?>">
                            <div class="dh-dau-the">
                                <h2 id="dh-don-<?= (int) $muc['id'] ?>">Đơn hàng #<?= (int) $muc['id'] ?></h2>
                                <span class="dh-trang-thai">
                                    <?= e($nhanTrangThai[(string) $muc['trang_thai']] ?? $muc['trang_thai']) ?>
                                </span>
                            </div>
                            <p><strong>Người nhận:</strong> <?= e($muc['ho_ten_nhan']) ?></p>
                            <p><strong>Ngày đặt:</strong> <?= e($muc['created_at']) ?></p>
                            <p><strong>Tổng tiền:</strong> <?= e(vnd((float) $muc['tong_tien'])) ?></p>
                            <a class="dh-lien-ket dh-nut-xem" href="don-hang.php?id=<?= (int) $muc['id'] ?>">
                                Xem chi tiết đơn hàng
                            </a>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <p><a class="dh-lien-ket" href="danh-sach.php">← Tiếp tục mua sắm</a></p>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/inc/footer.php'; ?>
