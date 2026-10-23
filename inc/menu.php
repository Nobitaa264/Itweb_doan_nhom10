<?php
/**
 * inc/menu.php
 * Menu điều hướng dùng chung cho toàn bộ website.
 */

$goc = $goc ?? './';
$trang = $trang ?? '';

$nguoiDung = $_SESSION['nguoi_dung'] ?? null;

function mucDangChon(string $tenTrang, string $trangHienTai): string
{
    return $tenTrang === $trangHienTai ? 'dang-chon' : '';
}

function ariaCurrent(string $tenTrang, string $trangHienTai): string
{
    return $tenTrang === $trangHienTai
        ? 'aria-current="page"'
        : '';
}
?>

<nav class="menu-chinh" aria-label="Điều hướng chính">
    <div class="bao">

        <button
            class="menu-chinh__nut"
            id="nut-menu"
            type="button"
            aria-expanded="false"
            aria-controls="danh-sach-menu"
        >
            Menu
        </button>

        <ul
            class="menu-chinh__danh-sach"
            id="danh-sach-menu"
        >

            <li>
                <a
                    class="<?= e(mucDangChon('index', $trang)) ?>"
                    href="<?= e($goc) ?>index.php"
                    <?= ariaCurrent('index', $trang) ?>
                >
                    Trang chủ
                </a>
            </li>

            <li>
                <a
                    class="<?= e(mucDangChon('danh-sach', $trang)) ?>"
                    href="<?= e($goc) ?>danh-sach.php"
                    <?= ariaCurrent('danh-sach', $trang) ?>
                >
                    Sản phẩm
                </a>
            </li>

            <li>
                <a
                    class="<?= e(mucDangChon('chi-tiet', $trang)) ?>"
                    href="<?= e($goc) ?>chi-tiet.php?id=SH001"
                    <?= ariaCurrent('chi-tiet', $trang) ?>
                >
                    Chi tiết
                </a>
            </li>

            <li>
                <a
                    class="<?= e(mucDangChon('gioi-thieu', $trang)) ?>"
                    href="<?= e($goc) ?>gioi-thieu.php"
                    <?= ariaCurrent('gioi-thieu', $trang) ?>
                >
                    Giới thiệu
                </a>
            </li>

            <li>
                <a
                    class="<?= e(mucDangChon('lien-he', $trang)) ?>"
                    href="<?= e($goc) ?>lien-he.php"
                    <?= ariaCurrent('lien-he', $trang) ?>
                >
                    Liên hệ
                </a>
            </li>

            <li>
                <a
                    href="<?= e($goc) ?>danh-sach.php?yeuthich=1"
                    aria-label="Xem sản phẩm yêu thích"
                >
                    ♥ Yêu thích
                </a>
            </li>

            <li>
                <a
                    href="<?= e($goc) ?>gio-hang.php"
                    aria-label="Xem giỏ hàng"
                >
                    Giỏ hàng
                    (<span><?= e($soMonGio ?? 0) ?></span>)
                </a>
            </li>

            <?php if (is_array($nguoiDung)): ?>
                <li>
                    <a
                        class="<?= e(mucDangChon('don-hang', $trang)) ?>"
                        href="<?= e($goc) ?>don-hang.php"
                        <?= ariaCurrent('don-hang', $trang) ?>
                    >
                        Đơn hàng
                    </a>
                </li>

                <?php if (($nguoiDung['vaiTro'] ?? $nguoiDung['vai_tro'] ?? '') === 'admin'): ?>
                    <li>
                        <a
                            class="<?= e(mucDangChon('quan-tri', $trang)) ?>"
                            href="<?= e($goc) ?>quan-tri.php"
                            <?= ariaCurrent('quan-tri', $trang) ?>
                        >
                            Quản trị
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <span>
                        Xin chào,
                        <?= e((string) ($nguoiDung['ho_ten'] ?? $nguoiDung['ten'] ?? 'Người dùng')) ?>
                    </span>
                </li>

                <li>
                    <a href="<?= e($goc) ?>dang-xuat.php">
                        Đăng xuất
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a
                        class="<?= e(mucDangChon('dang-nhap', $trang)) ?>"
                        href="<?= e($goc) ?>dang-nhap.php"
                        <?= ariaCurrent('dang-nhap', $trang) ?>
                    >
                        Đăng nhập
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </div>
</nav>