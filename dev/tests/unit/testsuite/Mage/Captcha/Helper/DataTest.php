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
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
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
        $filesystem = new Magento_Filesystem($adapterMock);
        return new Mage_Captcha_Helper_Data($app, $config, $filesystem);
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

        $config = $this->_getConfigStub();
        $config->expects($this->once())
            ->method('getModelInstance')
            ->with('Mage_Captcha_Model_Zend')
            ->will($this->returnValue(new Mage_Captcha_Model_Zend(array('formId' => 'user_create'))));

        $helper = $this->_getHelper($store, $config);
        $this->assertInstanceOf('Mage_Captcha_Model_Zend', $helper->getCaptcha('user_create'));
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

    /**
     * @covers Mage_Captcha_Helper_Data::getFonts
     */
    public function testGetFonts()
    {
        $option = $this->_getOptionStub();
        $option->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue(TESTS_TEMP_DIR));
        $config = $this->_getConfigStub();
        $config->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($option));

        $object = $this->_getHelper($this->_getStoreStub(), $config);
        $fonts = $object->getFonts();

        $this->assertEquals($fonts['linlibertine']['label'], 'LinLibertine');
        $this->assertEquals(
            $fonts['linlibertine']['path'],
            TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf'
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getImgDir
     * @covers Mage_Captcha_Helper_Data::getImgDir
     */
    public function testGetImgDir()
    {
        $captchaTmpDir = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'captcha';
        $option = $this->_getOptionStub();
        $option->expects($this->once())
            ->method('getDir')
            ->will($this->returnValue($captchaTmpDir));
        $config = $this->_getConfigStub();
        $config->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($option));

        $object = $this->_getHelper($this->_getStoreStub(), $config);
        $this->assertEquals(
            $object->getImgDir(),
            Magento_Filesystem::getPathFromArray(array($captchaTmpDir, 'captcha', 'base'))
            . Magento_Filesystem::DIRECTORY_SEPARATOR
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getImgUrl
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
            array('getNode', 'getModelInstance', 'getOptions'),
            array(), '', false
        );

        $config->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue(
                new SimpleXMLElement('<fonts><linlibertine><label>LinLibertine</label>'
                    . '<path>lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf</path></linlibertine></fonts>')));
        return $config;
    }

    /**
     * Create option stub
     *
     * @return Mage_Core_Model_Config_Options
     */
    protected function _getOptionStub()
    {
        $option = $this->getMock(
            'Mage_Core_Model_Config_Options',
            array('getDir'),
            array(), '', false
        );
        return $option;
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
