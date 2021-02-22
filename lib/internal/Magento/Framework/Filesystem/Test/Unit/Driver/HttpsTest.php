<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use \Magento\Framework\Filesystem\Driver\Https;

class HttpsTest extends \PHPUnit\Framework\TestCase
{
    public static $fSockOpen;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../_files/http_mock.php';
        self::$fSockOpen = 'resource';
    }

    public function testFileOpen()
    {
        $this->assertEquals(self::$fSockOpen, (new Https())->fileOpen('example.com', 'r'));
    }
}
