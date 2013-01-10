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
 * @category    Tools
 * @package     unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/Acl/Db/LoggerAbstract.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/Acl/Db/Logger/File.php';


require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/LoggerAbstract.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/Logger/File.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/System/FileManager.php';

class Tools_Migration_System_Configuration_Logger_FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    public function setUp()
    {
        $this->_fileManagerMock = $this->getMock('Tools_Migration_System_FileManager', array(), array(), '', false);
    }

    public function tearDown()
    {
        unset($this->_fileManagerMock);
    }

    public function testConstructWithValidFile()
    {
        new Tools_Migration_System_Configuration_Logger_File('report.log', $this->_fileManagerMock);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithInValidFile()
    {
        new Tools_Migration_System_Configuration_Logger_File(null, $this->_fileManagerMock);
    }

    public function testReport()
    {
        $model = new Tools_Migration_System_Configuration_Logger_File('report.log', $this->_fileManagerMock);
        $this->_fileManagerMock->expects($this->once())->method('write')->with($this->stringEndsWith('report.log'));
        $model->report();
    }
}
