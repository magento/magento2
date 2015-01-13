<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db\Logger;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/Logger/File.php';
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithValidFile()
    {
        new \Magento\Tools\Migration\Acl\Db\Logger\File(realpath(__DIR__ . '/../../../../../') . '/tmp/');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithInValidFile()
    {
        new \Magento\Tools\Migration\Acl\Db\Logger\File(null);
    }
}
