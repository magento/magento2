<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filesystem\Driver;

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
