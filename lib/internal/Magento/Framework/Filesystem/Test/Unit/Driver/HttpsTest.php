<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use \Magento\Framework\Filesystem\Driver\Https;

class HttpsTest extends \PHPUnit_Framework_TestCase
{
    public static $fSockOpen;

    public function setUp()
    {
        require_once __DIR__ . '/../_files/http_mock.php';
        self::$fSockOpen = 'resource';
    }

    public function testFileOpen()
    {
        $this->assertEquals(self::$fSockOpen, (new Https())->fileOpen('example.com', 'r'));
    }
}
