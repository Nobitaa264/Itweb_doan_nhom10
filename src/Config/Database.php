<?php

declare(strict_types=1);

namespace App\Config;

use PDO;

/**
 * Tuong thich voi cac trang cu dang goi Database::connect().
 * Tat ca ket noi MySQL deu di qua ham db() trong inc/db.php,
 * de dung chung phpdotenv, tai khoan va mot ket noi PDO moi request.
 */
final class Database
{
    public static function connect(): PDO
    {
        require_once dirname(__DIR__, 2) . '/inc/db.php';

        return \db();
    }
}
