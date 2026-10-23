<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/config.php';
// Yêu cầu quyền admin để chỉnh sửa sản phẩm
$user = yeuCauQuyen('admin');
use App\Config\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Phương thức không được hỗ trợ.');
}

$token = (string) ($_POST['_token'] ?? '');
$tokenSession = (string) ($_SESSION['csrf_token'] ?? '');

if ($tokenSession === '' || !hash_equals($tokenSession, $token)) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Phiên làm việc không hợp lệ. Vui lòng thử lại.',
    ];

    header('Location: quan-tri.php');
    exit;
}

$id = trim((string) ($_POST['id'] ?? ''));

if (!preg_match('/^[A-Z]{2}[0-9]{3}$/', $id)) {
    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Mã sản phẩm không hợp lệ.',
    ];

    header('Location: quan-tri.php');
    exit;
}

try {
    $pdo = Database::connect();

    $stmt = $pdo->prepare(
        'DELETE FROM san_pham WHERE id = :id'
    );

    $stmt->execute(['id' => $id]);

    $_SESSION['flash'] = [
        'loai' => $stmt->rowCount() > 0 ? 'thanh-cong' : 'loi',
        'noiDung' => $stmt->rowCount() > 0
            ? 'Đã xóa sản phẩm thành công.'
            : 'Không tìm thấy sản phẩm cần xóa.',
    ];
} catch (\PDOException $e) {
    error_log('[XOA_SAN_PHAM] ' . $e->getMessage());

    $_SESSION['flash'] = [
        'loai' => 'loi',
        'noiDung' => 'Không thể xóa sản phẩm. Có thể sản phẩm đang được dữ liệu khác tham chiếu.',
    ];
}

header('Location: quan-tri.php');
exit;
