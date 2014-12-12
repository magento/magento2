<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
