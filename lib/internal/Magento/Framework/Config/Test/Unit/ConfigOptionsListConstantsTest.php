<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\ConfigOptionsListConstants;
use PHPUnit\Framework\TestCase;

class ConfigOptionsListConstantsTest extends TestCase
{
    /**
     * The MySQL SSL driver option keys must resolve to the attributes of the running PHP version.
     *
     * PHP 8.5 moved Pdo\Mysql::ATTR_DIRECT_QUERY out of the driver specific attribute range, shifting
     * every following attribute down by one. A hardcoded integer therefore addresses a different
     * attribute depending on the PHP version it runs on.
     */
    public function testMysqlSslKeysMatchPdoAttributes(): void
    {
        if (\PHP_VERSION_ID >= 80400) {
            $this->assertSame(\Pdo\Mysql::ATTR_SSL_KEY, ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY);
            $this->assertSame(\Pdo\Mysql::ATTR_SSL_CERT, ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT);
            $this->assertSame(\Pdo\Mysql::ATTR_SSL_CA, ConfigOptionsListConstants::KEY_MYSQL_SSL_CA);
            $this->assertSame(
                \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT,
                ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY
            );

            return;
        }

        $this->assertSame(\PDO::MYSQL_ATTR_SSL_KEY, ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY);
        $this->assertSame(\PDO::MYSQL_ATTR_SSL_CERT, ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT);
        $this->assertSame(\PDO::MYSQL_ATTR_SSL_CA, ConfigOptionsListConstants::KEY_MYSQL_SSL_CA);
        $this->assertSame(
            \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT,
            ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY
        );
    }
}
