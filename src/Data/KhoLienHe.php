<?php

declare(strict_types=1);

/**
 * KhoLienHe.php
 *
 * Lớp truy cập dữ liệu các yêu cầu liên hệ.
 * Đây là nơi duy nhất đọc file storage/lien-he.jsonl.
 */

namespace App\Data;

use JsonException;
use RuntimeException;

final class KhoLienHe
{
    private string $duongDan;

    public function __construct()
    {
        $this->duongDan = __DIR__
            . '/../../storage/lien-he.jsonl';
    }

    /**
     * Lấy toàn bộ liên hệ, mới nhất trước.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tatCa(): array
    {
        if (!is_file($this->duongDan)) {
            return [];
        }

        $cacDong = file(
            $this->duongDan,
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        if ($cacDong === false) {
            throw new RuntimeException(
                'Không thể đọc dữ liệu liên hệ.'
            );
        }

        $danhSach = [];

        foreach ($cacDong as $dong) {
            try {
                $banGhi = json_decode(
                    $dong,
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if (is_array($banGhi)) {
                    $danhSach[] = $banGhi;
                }
            } catch (JsonException) {
                error_log(
                    '[LIEN_HE] Bỏ qua một dòng JSONL không hợp lệ.'
                );
            }
        }

        usort(
            $danhSach,
            static function (
                array $a,
                array $b
            ): int {
                return strcmp(
                    (string) ($b['thoiGian'] ?? ''),
                    (string) ($a['thoiGian'] ?? '')
                );
            }
        );

        return $danhSach;
    }
}