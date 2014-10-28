<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Migration\System\Configuration\Logger;


require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//Acl/Db/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//Acl/Db/Logger/File.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//System/Configuration/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration//System/Configuration/Logger/File.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
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
            array(),
            array(),
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
