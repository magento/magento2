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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/LoggerAbstract.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/Logger/File.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/Logger/Console.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/Configuration/Logger/Factory.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/System/FileManager.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/System/FileReader.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../../')
    . '/tools/migration/System/WriterInterface.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../../') . '/tools/migration/System/Writer/Memory.php';


class Tools_Migration_System_Configuration_Logger_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_System_Configuration_Logger_Factory
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    public function setUp()
    {
        $this->_model = new Tools_Migration_System_Configuration_Logger_Factory();
        $this->_fileManagerMock = $this->getMock('Tools_Migration_System_FileManager', array(), array(), '', false);
    }

    public function tearDown()
    {
        unset($this->_model);
        unset($this->_fileManagerMock);
    }

    /**
     * @return array
     */
    public function getLoggerDataProvider()
    {
        return array(
            array('Tools_Migration_System_Configuration_Logger_File', 'file', 'report.log'),
            array('Tools_Migration_System_Configuration_Logger_Console', 'console', null),
            array('Tools_Migration_System_Configuration_Logger_Console', 'dummy', null),
        );
    }

    /**
     * @param string $expectedInstance
     * @param string $loggerType
     * @param string $path
     * @dataProvider getLoggerDataProvider
     */
    public function testGetLogger($expectedInstance, $loggerType, $path)
    {
        $this->assertInstanceOf($expectedInstance,
            $this->_model->getLogger($loggerType, $path, $this->_fileManagerMock)
        );
    }
}

