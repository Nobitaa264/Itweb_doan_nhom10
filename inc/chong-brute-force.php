<?php

declare(strict_types=1);

/**
 * TH7 - Gioi han thu dang nhap + ghi nhat ky bao mat.
 *
 * Luu bo dem va nhat ky NGOAI web root: D:\StyleHub_private\security\.
 * Khong luu mat khau, session ID, CSRF token trong nhat ky.
 * Dung flock() de cap nhat bo dem an toan khi co nhieu request.
 *
 * 5 lan thu / tai khoan / 15 phut -> khoa 15 phut.
 * 15 lan thu / IP / 15 phut -> khoa 15 phut.
 * Khong tin vao X-Forwarded-For khi chua cau hinh trusted proxy.
 */
const TH7_LOGIN_CUA_SO_GIAY = 900;
const TH7_LOGIN_KHOA_GIAY = 900;
const TH7_LOGIN_TOI_DA_TAI_KHOAN = 5;
const TH7_LOGIN_TOI_DA_IP = 15;

function th7LoginThuMucBaoMat(): string
{
    // Giong duong dan .env duoc su dung trong inc/db.php.
    $ngoaiWeb = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'StyleHub_private';
    if (!is_dir($ngoaiWeb) || !is_writable($ngoaiWeb)) {
        throw new RuntimeException('Thu muc cau hinh bao mat khong san sang.');
    }

    $thuMuc = $ngoaiWeb . DIRECTORY_SEPARATOR . 'security';
    if (!is_dir($thuMuc) && !@mkdir($thuMuc, 0700, true) && !is_dir($thuMuc)) {
        throw new RuntimeException('Khong tao duoc thu muc nhat ky bao mat.');
    }
    if (!is_writable($thuMuc)) {
        throw new RuntimeException('Khong ghi duoc thu muc nhat ky bao mat.');
    }
    return $thuMuc;
}

function th7LoginIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false
        ? $ip
        : 'unknown';
}

function th7LoginKhoaTaiKhoan(string $tenDangNhap): string
{
    // Chi luu ma bam, khong luu ten dang nhap trong bo dem.
    return 'u:' . hash('sha256', strtolower(trim($tenDangNhap)));
}

function th7LoginKhoaIp(string $ip): string
{
    return 'ip:' . hash('sha256', $ip);
}

/**
 * Doc/sua trang thai duoi mot khoa file doc quyen.
 * Neu tep bi loi, khong reset ve 0 (fail-closed) de tranh vuot gioi han.
 */
function th7LoginSuaTrangThai(callable $capNhat): mixed
{
    $tep = th7LoginThuMucBaoMat() . DIRECTORY_SEPARATOR . 'login-attempts.json';
    $handle = @fopen($tep, 'c+b');
    if ($handle === false) {
        throw new RuntimeException('Khong mo duoc bo dem dang nhap.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Khong khoa duoc bo dem dang nhap.');
        }
        $thongTin = fstat($handle);
        if (!is_array($thongTin) || (int) ($thongTin['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new RuntimeException('Bo dem dang nhap vuot kich thuoc cho phep.');
        }

        rewind($handle);
        $raw = stream_get_contents($handle);
        if (!is_string($raw)) {
            throw new RuntimeException('Khong doc duoc bo dem dang nhap.');
        }
        $trangThai = $raw === '' ? [] : json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($trangThai)) {
            throw new RuntimeException('Bo dem dang nhap khong hop le.');
        }

        $now = time();
        foreach ($trangThai as $khoa => $item) {
            if (!is_array($item)) {
                throw new RuntimeException('Du lieu bo dem khong hop le.');
            }
            $cacLan = array_values(array_filter(
                $item['times'] ?? [],
                static fn (mixed $t): bool => is_int($t) && $t > $now - TH7_LOGIN_CUA_SO_GIAY && $t <= $now
            ));
            $khoaDen = (int) ($item['locked_until'] ?? 0);
            if ($cacLan === [] && $khoaDen <= $now) {
                unset($trangThai[$khoa]);
            } else {
                $trangThai[$khoa] = ['times' => $cacLan, 'locked_until' => $khoaDen];
            }
        }
        if (count($trangThai) > 10000) {
            throw new RuntimeException('Bo dem qua tai.');
        }

        $ketQua = $capNhat($trangThai, $now);
        $json = json_encode($trangThai, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || strlen($json) > 2 * 1024 * 1024) {
            throw new RuntimeException('Bo dem dang nhap qua lon.');
        }

        rewind($handle);
        $daGhi = 0;
        while ($daGhi < strlen($json)) {
            $n = fwrite($handle, substr($json, $daGhi));
            if ($n === false || $n === 0) {
                throw new RuntimeException('Khong ghi duoc bo dem dang nhap.');
            }
            $daGhi += $n;
        }
        if (!ftruncate($handle, strlen($json)) || !fflush($handle)) {
            throw new RuntimeException('Khong luu duoc bo dem dang nhap.');
        }
        return $ketQua;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * Dang ky mot luot thu TRUOC KHI kiem tra mat khau, tranh request song song
 * vuot qua gioi han. Tra ve so giay phai cho neu dang bi khoa, 0 neu duoc phep.
 */
function th7LoginBatDauThu(string $tenDangNhap, string $ip): int
{
    $khoaTaiKhoan = th7LoginKhoaTaiKhoan($tenDangNhap);
    $khoaIp = th7LoginKhoaIp($ip);

    return th7LoginSuaTrangThai(
        static function (array &$trangThai, int $now) use ($khoaTaiKhoan, $khoaIp): int {
            foreach ([$khoaTaiKhoan, $khoaIp] as $key) {
                $den = (int) ($trangThai[$key]['locked_until'] ?? 0);
                if ($den > $now) {
                    return $den - $now;
                }
            }

            foreach ([$khoaTaiKhoan => TH7_LOGIN_TOI_DA_TAI_KHOAN,
                      $khoaIp => TH7_LOGIN_TOI_DA_IP] as $key => $gioiHan) {
                $item = $trangThai[$key] ?? ['times' => [], 'locked_until' => 0];
                $item['times'][] = $now;
                if (count($item['times']) >= $gioiHan) {
                    $item['locked_until'] = $now + TH7_LOGIN_KHOA_GIAY;
                }
                $trangThai[$key] = $item;
            }
            return 0;
        }
    );
}

/** Khi xac thuc dung, xoa bo dem cua tai khoan; IP van duoc gioi han. */
function th7LoginThanhCong(string $tenDangNhap): void
{
    $key = th7LoginKhoaTaiKhoan($tenDangNhap);
    th7LoginSuaTrangThai(
        static function (array &$trangThai, int $now) use ($key): null {
            unset($trangThai[$key]);
            return null;
        }
    );
}

/**
 * Log JSON Lines. Luu ten dang nhap (da loc ky tu dieu khien) va IP de truy vet.
 * Khong bao gio log mat khau, token, cookie hay hash mat khau.
 */
function th7LoginGhiNhatKy(string $suKien, string $tenDangNhap, string $ip): void
{
    if (!in_array($suKien, ['login_failed', 'login_blocked', 'login_success', 'login_error'], true)) {
        throw new InvalidArgumentException('Su kien bao mat khong hop le.');
    }
    $tenAnToan = preg_replace('/[^a-zA-Z0-9_.-]/', '_', substr($tenDangNhap, 0, 100));
    $banGhi = [
        'time_utc' => gmdate('c'),
        'event' => $suKien,
        'username' => $tenAnToan,
        'ip' => $ip,
    ];
    $line = json_encode($banGhi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $tep = th7LoginThuMucBaoMat() . DIRECTORY_SEPARATOR . 'login-events.jsonl';
    if (file_put_contents($tep, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        throw new RuntimeException('Khong ghi duoc nhat ky dang nhap.');
    }
}
