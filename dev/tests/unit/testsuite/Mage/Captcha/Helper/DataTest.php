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
 * @package     Mage_Captcha
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Captcha_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Fixture for testing getFonts()
     */
    const FONT_FIXTURE = '<fonts><font_code><label>Label</label><path>path/to/fixture.ttf</path></font_code></fonts>';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    protected function setUp()
    {
        $this->_dirMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
    }

    /**
     * Return helper to be tested
     *
     * @param Mage_Core_Model_Store $store
     * @param Mage_Core_Model_Config $config
     * @return Mage_Captcha_Helper_Data
     */
    protected function _getHelper($store, $config)
    {
        $app = $this->getMockBuilder('Mage_Core_Model_App')
            ->disableOriginalConstructor()
            ->getMock();
        $app->expects($this->any())
            ->method('getWebsite')
            ->will($this->returnValue($this->_getWebsiteStub()));
        $app->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $adapterMock = $this->getMockBuilder('Magento_Filesystem_Adapter_Local')
            ->getMock();
        $adapterMock->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);

        $context = $this->getMock('Mage_Core_Helper_Context', array(), array(), '', false);

        return new Mage_Captcha_Helper_Data($context, $this->_dirMock, $app, $config, $filesystem);
    }

    /**
     * @covers Mage_Captcha_Helper_Data::getCaptcha
     */
    public function testGetCaptcha()
    {
        $store = $this->_getStoreStub();
        $store->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(false));

        $store->expects($this->once())
            ->method('getConfig')
            ->with('customer/captcha/type')
            ->will($this->returnValue('zend'));

        $objectManager = $this->getMock('Magento_ObjectManager');
        $config = $this->_getConfigStub();
        $config->expects($this->once())
            ->method('getModelInstance')
            ->with('Mage_Captcha_Model_Zend')
            ->will($this->returnValue(
            new Mage_Captcha_Model_Default($objectManager, array('formId' => 'user_create'))));

        $helper = $this->_getHelper($store, $config);
        $this->assertInstanceOf('Mage_Captcha_Model_Default', $helper->getCaptcha('user_create'));
    }

    /**
     * @covers Mage_Captcha_Helper_Data::getConfigNode
     */
    public function testGetConfigNode()
    {
        $store = $this->_getStoreStub();
        $store->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(false));

        $store->expects($this->once())
            ->method('getConfig')
            ->with('customer/captcha/enable')
            ->will($this->returnValue('1'));
        $object = $this->_getHelper($store, $this->_getConfigStub());
        $object->getConfigNode('enable');
    }

    public function testGetFonts()
    {
        $this->_dirMock->expects($this->once())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::LIB)
            ->will($this->returnValue(TESTS_TEMP_DIR . '/lib'));

        $object = $this->_getHelper($this->_getStoreStub(), $this->_getConfigStub());
        $fonts = $object->getFonts();
        $this->assertArrayHasKey('font_code', $fonts); // fixture
        $this->assertArrayHasKey('label', $fonts['font_code']);
        $this->assertArrayHasKey('path', $fonts['font_code']);
        $this->assertEquals('Label', $fonts['font_code']['label']);
        $this->assertStringStartsWith(TESTS_TEMP_DIR, $fonts['font_code']['path']);
        $this->assertStringEndsWith('path/to/fixture.ttf', $fonts['font_code']['path']);
    }

    /**
     * @covers Mage_Captcha_Model_Default::getImgDir
     * @covers Mage_Captcha_Helper_Data::getImgDir
     */
    public function testGetImgDir()
    {
        $this->_dirMock->expects($this->once())
            ->method('getDir')
            ->with(Mage_Core_Model_Dir::MEDIA)
            ->will($this->returnValue(TESTS_TEMP_DIR . '/media'));

        $object = $this->_getHelper($this->_getStoreStub(), $this->_getConfigStub());
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/captcha');
        $result = $object->getImgDir();
        $result = str_replace('/', DIRECTORY_SEPARATOR, $result);
        $this->assertStringStartsWith(TESTS_TEMP_DIR, $result);
        $this->assertStringEndsWith('captcha' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR, $result);
    }

    /**
     * @covers Mage_Captcha_Model_Default::getImgUrl
     * @covers Mage_Captcha_Helper_Data::getImgUrl
     */
    public function testGetImgUrl()
    {
        $object = $this->_getHelper($this->_getStoreStub(), $this->_getConfigStub());
        $this->assertEquals($object->getImgUrl(), 'http://localhost/pub/media/captcha/base/');
    }

    /**
     * Create Config Stub
     *
     * @return Mage_Core_Model_Config
     */
    protected function _getConfigStub()
    {
        $config = $this->getMock(
            'Mage_Core_Model_Config',
            array('getNode', 'getModelInstance'),
            array(), '', false
        );

        $config->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue(new SimpleXMLElement(self::FONT_FIXTURE)));
        return $config;
    }

    /**
     * Create Website Stub
     *
     * @return Mage_Core_Model_Website
     */
    protected function _getWebsiteStub()
    {
        $website = $this->getMock(
            'Mage_Core_Model_Website',
            array('getCode'),
            array(), '', false
        );

        $website->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('base'));

        return $website;
    }

    /**
     * Create store stub
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock(
            'Mage_Core_Model_Store',
            array('isAdmin', 'getConfig', 'getBaseUrl'),
            array(), '', false
        );

        $store->expects($this->any())
            ->method('getBaseUrl')
            ->will($this->returnValue('http://localhost/pub/media/'));

        return $store;
    }
}
