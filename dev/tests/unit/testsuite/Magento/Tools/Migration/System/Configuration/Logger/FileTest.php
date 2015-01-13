<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Logger;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//Acl/Db/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//Acl/Db/Logger/File.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//System/Configuration/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//System/Configuration/Logger/File.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//System/FileManager.php';
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    protected function setUp()
    {
        $this->_fileManagerMock = $this->getMock(
            'Magento\Tools\Migration\System\FileManager',
            [],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        unset($this->_fileManagerMock);
    }

    public function testConstructWithValidFile()
    {
        new \Magento\Tools\Migration\System\Configuration\Logger\File('report.log', $this->_fileManagerMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithInValidFile()
    {
        new \Magento\Tools\Migration\System\Configuration\Logger\File(null, $this->_fileManagerMock);
    }

    public function testReport()
    {
        $model = new \Magento\Tools\Migration\System\Configuration\Logger\File('report.log', $this->_fileManagerMock);
        $this->_fileManagerMock->expects($this->once())->method('write')->with($this->stringEndsWith('report.log'));
        $model->report();
    }
}
