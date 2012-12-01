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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageTest extends PHPUnit_Framework_TestCase
{
    public function testIsInstalled()
    {
        $this->assertTrue(Mage::isInstalled());
    }

    /**
     * @param int|null $level
     * @param string $file
     * @param bool $forceLog
     * @param int $expectedLevel
     * @param string $expectedKey
     * @param bool $expectsAddLog
     * @dataProvider logDataProvider
     * @throws Exception
     */
    public function testLog($level, $file, $forceLog, $expectedLevel, $expectedKey, $expectsAddLog)
    {
        $message = uniqid();
        $objectManager = Mage::getObjectManager();
        /** @var $objectManager Magento_ObjectManager_Zend|PHPUnit_Framework_MockObject_MockObject */
        $mock = $this->getMock('Magento_ObjectManager_Zend', array('get'), array(), '', false);
        /** @var $logger Mage_Core_Model_Logger|PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMock('Mage_Core_Model_Logger', array('log', 'addStreamLog'), array(), '', false);
        Mage::initializeObjectManager(null, $mock);
        try {
            $mock->expects($this->any())->method('get')->will($this->returnValue($logger));
            $logger->expects($this->once())->method('log')->with($message, $expectedLevel, $expectedKey);
            if ($expectsAddLog) {
                $logger->expects($this->once())->method('addStreamLog');
            }
            Mage::log($message, $level, $file, $forceLog);
            Mage::initializeObjectManager(null, $objectManager);
        } catch (Exception $e) {
            Mage::initializeObjectManager(null, $objectManager);
            throw $e;
        }

    }

    /**
     * @return array
     */
    public function logDataProvider()
    {
        return array(
            array(null, '', false, Zend_Log::DEBUG, Mage_Core_Model_Logger::LOGGER_SYSTEM, false),
            array(Zend_Log::CRIT, 'system.log', true, Zend_Log::CRIT, Mage_Core_Model_Logger::LOGGER_SYSTEM, false),
            array(null, 'exception.log', false, Zend_Log::DEBUG, Mage_Core_Model_Logger::LOGGER_EXCEPTION, false),
            array(null, 'custom.log', false, Zend_Log::DEBUG, 'custom.log', true, false),
            array(null, 'exception.log', true, Zend_Log::DEBUG, Mage_Core_Model_Logger::LOGGER_EXCEPTION, true),
        );
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture current_store dev/log/file php://output
     * @link http://us3.php.net/manual/en/wrappers.php
     */
    public function testLogWrapper()
    {
        // @magentoConfigFixture is applied after initialization, so we need to do this again
        Magento_Test_Bootstrap::getInstance()->reinitialize();
        $this->expectOutputRegex('/test/');
        Mage::log('test');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLogWrapperDirectly()
    {
        $this->expectOutputRegex('/test/');
        Mage::log('test', null, 'php://output');
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture global/log/core/writer_model Zend_Log_Writer_Mail
     * @magentoAppIsolation enabled
     */
    public function testLogUnsupportedWrapper()
    {
        // initialize again, because config fixture is applied after initialization
        Magento_Test_Bootstrap::getInstance()->reinitialize();
        $logEntry = microtime();
        Mage::log($logEntry);
        $logFile = Mage::getBaseDir('log') . '/system.log';
        $this->assertFileExists($logFile);
        $this->assertContains($logEntry, file_get_contents($logFile));
    }

    /**
     * @magentoConfigFixture current_store dev/log/active 1
     * @magentoConfigFixture current_store dev/log/exception_file php://output
     * @magentoAppIsolation enabled
     */
    public function testLogException()
    {
        // reinitialization is needed here, too
        Magento_Test_Bootstrap::getInstance()->reinitialize();
        $msg = uniqid();
        $exception = new Exception((string)$msg);
        Mage::logException($exception);
        $this->expectOutputRegex('/' . $msg . '/');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testReset()
    {
        Mage::setRoot(dirname(__FILE__));
        $this->assertNotNull(Mage::getRoot());
        Mage::reset();
        $this->assertNull(Mage::getRoot());
    }

    /**
     * @magentoAppIsolation enabled
     *
     */
    public function testGetDesign()
    {
        $design = Mage::getDesign();
        $this->assertEquals('frontend', $design->getArea());
        $this->assertSame(Mage::getDesign(), $design);
    }

    /**
     * @param string $classId
     * @param string $expectedClassName
     * @dataProvider getModelDataProvider
     */
    public function testGetModel($classId, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getModel($classId));
    }

    /**
     * @return array
     */
    public function getModelDataProvider()
    {
        return array(
            array('Mage_Core_Model_Config', 'Mage_Core_Model_Config')
        );
    }

    /**
     * @param string $classId
     * @param string $expectedClassName
     * @dataProvider getResourceModelDataProvider
     */
    public function testGetResourceModel($classId, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getResourceModel($classId));
    }

    /**
     * @return array
     */
    public function getResourceModelDataProvider()
    {
        return array(
            array('Mage_Core_Model_Resource_Config', 'Mage_Core_Model_Resource_Config')
        );
    }

    /**
     * @param string $module
     * @param string $expectedClassName
     * @dataProvider getResourceHelperDataProvider
     */
    public function testGetResourceHelper($module, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::getResourceHelper($module));
    }

    /**
     * @return array
     */
    public function getResourceHelperDataProvider()
    {
        return array(
            array('Mage_Core', 'Mage_Core_Model_Resource_Helper_Abstract')
        );
    }

    /**
     * @param string $classId
     * @param string $expectedClassName
     * @dataProvider helperDataProvider
     */
    public function testHelper($classId, $expectedClassName)
    {
        $this->assertInstanceOf($expectedClassName, Mage::helper($classId));
    }

    /**
     * @return array
     */
    public function helperDataProvider()
    {
        return array(
            'module name' => array('Mage_Core',           'Mage_Core_Helper_Data'),
            'class name'  => array('Mage_Core_Helper_Js', 'Mage_Core_Helper_Js'),
        );
    }
}
