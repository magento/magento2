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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Captcha_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Captcha_Helper_Data
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_object = new Mage_Captcha_Helper_Data();
        $this->_object->setConfig($this->_getConfigStub());
        $this->_object->setWebsite($this->_getWebsiteStub());
        $this->_object->setStore($this->_getStoreStub());
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
            ->with('customer/captcha/type', null)
            ->will($this->returnValue('zend'));
        $this->_object->setStore($store);

        $config = $this->_getConfigStub();
        $config->expects($this->once())
            ->method('getModelInstance')
            ->with('Mage_Captcha_Model_Zend',
                array(
                    'params' => array('formId' => 'user_create', 'helper' => $this->_object)
                )
            )
            ->will($this->returnValue(new Mage_Captcha_Model_Zend(array('formId' => 'user_create'))));
        $this->_object->setConfig($config);

        $this->assertInstanceOf('Mage_Captcha_Model_Zend', $this->_object->getCaptcha('user_create'));
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
            ->with('customer/captcha/enable', null)
            ->will($this->returnValue('1'));
        $this->_object->setStore($store);
        $this->_object->getConfigNode('enable');
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
        $this->_object->setOption($option);

        $fonts = $this->_object->getFonts();

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
        $this->_object->setOption($option);

        $this->assertEquals(
            $this->_object->getImgDir(),
            $captchaTmpDir . DIRECTORY_SEPARATOR . 'captcha' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR
        );
    }

    /**
     * @covers Mage_Captcha_Model_Zend::getImgUrl
     * @covers Mage_Captcha_Helper_Data::getImgUrl
     */
    public function testGetImgUrl()
    {
        $this->assertEquals($this->_object->getImgUrl(), 'http://localhost/pub/media/captcha/base/');
    }

    /**
     * Create Config Stub
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
            ->will($this->returnValue(
                new SimpleXMLElement('<fonts><linlibertine><label>LinLibertine</label>'
                    . '<path>lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf</path></linlibertine></fonts>')));
        return $config;
    }

    /**
     * Create option stub
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
