<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\DB\Charset;

/**
 * Single source of truth for default charset and collation by SQL version key.
 * Used by Table.php (declarative schema), Mysql adapter (default init statement), and Setup.
 */
class DefaultCharsetCollationMap
{
    /**#@+
     * Version keys for charset/collation map.
     */
    public const VERSION_10_4 = '10.4.';
    public const VERSION_10_6 = '10.6.';
    public const VERSION_10_11 = '10.11.';
    public const VERSION_11_4 = '11.4.';
    public const VERSION_11_8 = '11.8.';
    public const VERSION_12_2 = '12.2.';
    public const VERSION_12_3 = '12.3.';
    public const VERSION_MYSQL_8_29 = 'mysql_8_29';
    public const VERSION_DEFAULT = 'default';

    /**#@-*/

    /**
     * Charset by version key (matches SqlVersionProvider return values).
     *
     * @var array<string, string>
     */
    private static array $charset = [
        self::VERSION_10_4 => 'utf8mb4',
        self::VERSION_10_6 => 'utf8mb4',
        self::VERSION_10_11 => 'utf8mb4',
        self::VERSION_11_4 => 'utf8mb4',
        self::VERSION_11_8 => 'utf8mb4',
        self::VERSION_12_2 => 'utf8mb4',
        self::VERSION_12_3 => 'utf8mb4',
        self::VERSION_MYSQL_8_29 => 'utf8mb4',
        self::VERSION_DEFAULT => 'utf8',
    ];

    /**
     * Collation by version key. MySQL >= 8.0.29 uses utf8mb4_0900_ai_ci to match table charset.
     *
     * @var array<string, string>
     */
    private static array $collation = [
        self::VERSION_10_4 => 'utf8mb4_general_ci',
        self::VERSION_10_6 => 'utf8mb4_general_ci',
        self::VERSION_10_11 => 'utf8mb4_general_ci',
        self::VERSION_11_4 => 'utf8mb4_general_ci',
        self::VERSION_11_8 => 'utf8mb4_general_ci',
        self::VERSION_12_2 => 'utf8mb4_general_ci',
        self::VERSION_12_3 => 'utf8mb4_general_ci',
        self::VERSION_MYSQL_8_29 => 'utf8mb4_general_ci',
        self::VERSION_DEFAULT => 'utf8_general_ci',
    ];

    /**
     * Get default charset for a version key.
     *
     * @param string $versionKey One of '10.4.', '10.6.', '10.11.', '11.4.', 'mysql_8_29', 'default'
     * @return string
     */
    public static function getCharset(string $versionKey): string
    {
        return self::$charset[$versionKey] ?? self::$charset[self::VERSION_DEFAULT];
    }

    /**
     * Get default collation for a version key.
     *
     * @param string $versionKey One of '10.4.', '10.6.', '10.11.', '11.4.', 'mysql_8_29', 'default'
     * @return string
     */
    public static function getCollation(string $versionKey): string
    {
        return self::$collation[$versionKey] ?? self::$collation[self::VERSION_DEFAULT];
    }

    /**
     * Parse DB version string (e.g. from SELECT @@version) to version key for this map.
     *
     * @param string $versionString e.g. "8.0.30" or "10.4.0-MariaDB"
     * @return string One of '10.4.', '10.6.', '10.11.', '11.4.', 'mysql_8_29', 'default'
     */
    public static function parseVersionToKey(string $versionString): string
    {
        $versionString = trim($versionString);
        if ($versionString === '' || !preg_match('/(\d+\.\d+(?:\.\d+)?)/', $versionString, $match)) {
            return self::VERSION_DEFAULT;
        }
        $versionNum = $match[1];
        if (str_contains(strtolower($versionString), 'mariadb')) {
            return self::parseMariaDbVersionToKey($versionNum);
        }
        return self::parseMysqlVersionToKey($versionNum);
    }

    /**
     * Map MariaDB version string to version key.
     *
     * @param string $versionNum e.g. "10.4.0"
     * @return string
     */
    private static function parseMariaDbVersionToKey(string $versionNum): string
    {
        if (version_compare($versionNum, '11.4.0', '>=')) {
            return self::VERSION_11_4;
        }
        if (version_compare($versionNum, '10.11.0', '>=')) {
            return self::VERSION_10_11;
        }
        if (version_compare($versionNum, '10.6.0', '>=')) {
            return self::VERSION_10_6;
        }
        if (version_compare($versionNum, '10.4.0', '>=')) {
            return self::VERSION_10_4;
        }
        return self::VERSION_DEFAULT;
    }

    /**
     * Map MySQL version string to version key.
     *
     * @param string $versionNum e.g. "8.0.30" or "8.4.0"
     * @return string
     */
    private static function parseMysqlVersionToKey(string $versionNum): string
    {
        if (version_compare($versionNum, '8.0.29', '>=')) {
            return self::VERSION_MYSQL_8_29;
        }
        return self::VERSION_DEFAULT;
    }

    /**
     * Build SET NAMES init statement from DB version string (e.g. from SELECT @@version).
     *
     * @param string $versionString e.g. "8.0.30" or "10.4.0-MariaDB"
     * @return string e.g. "SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci"
     */
    public static function getInitStatementFromVersionString(string $versionString): string
    {
        $key = self::parseVersionToKey($versionString);
        return sprintf('SET NAMES %s COLLATE %s', self::getCharset($key), self::getCollation($key));
    }
}
