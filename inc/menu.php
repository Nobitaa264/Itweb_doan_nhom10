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

            <li>
                
<?php if (is_array($nguoiDung)): ?>

    <span>
        Xin chào,
        <?= e((string) ($nguoiDung['ten'] ?? 'Người dùng')) ?>
    </span>

    <?php if (($nguoiDung['vaiTro'] ?? '') === 'admin'): ?>
        <a href="<?= e($goc) ?>quan-tri.php">
            Quản trị
        </a>
    <?php endif; ?>

    <a href="<?= e($goc) ?>dang-xuat.php">
        Đăng xuất
    </a>

<?php else: ?>

    <a href="<?= e($goc) ?>dang-nhap.php">
        Đăng nhập
    </a>

<?php endif; ?>

            </li>

        </ul>
    </div>
</nav>