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
namespace Magento\Captcha\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    protected function setUp()
    {
        $this->_filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
    }

    protected function _getHelper($store, $config, $factory)
    {
        $storeManager = $this->getMockBuilder(
            'Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($this->_getWebsiteStub()));
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $context = $this->getMock('Magento\Framework\App\Helper\Context', array(), array(), '', false);

        return new \Magento\Captcha\Helper\Data($context, $storeManager, $config, $this->_filesystem, $factory);
    }

    /**
     * @covers \Magento\Captcha\Helper\Data::getCaptcha
     */
    public function testGetCaptcha()
    {
        $store = $this->_getStoreStub();
        $config = $this->_getConfigStub();
        $config->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'customer/captcha/type'
        )->will(
            $this->returnValue('zend')
        );

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false);
        $factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Zend')
        )->will(
            $this->returnValue(
                new \Magento\Captcha\Model\DefaultModel(
                    $this->getMock('Magento\Framework\Session\SessionManager', array(), array(), '', false),
                    $this->getMock('Magento\Captcha\Helper\Data', array(), array(), '', false),
                    $this->getMock('Magento\Captcha\Model\Resource\LogFactory', array(), array(), '', false),
                    'user_create'
                )
            )
        );

        $helper = $this->_getHelper($store, $config, $factoryMock);
        $this->assertInstanceOf('Magento\Captcha\Model\DefaultModel', $helper->getCaptcha('user_create'));
    }

    /**
     * @covers \Magento\Captcha\Helper\Data::getConfig
     */
    public function testGetConfigNode()
    {
        $store = $this->_getStoreStub();
        $config = $this->_getConfigStub();
        $config->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'customer/captcha/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue('1')
        );

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false);
        $object = $this->_getHelper($store, $config, $factoryMock);
        $object->getConfig('enable');
    }

    public function testGetFonts()
    {
        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getPath'
        )->with(
            \Magento\Framework\App\Filesystem::LIB_INTERNAL
        )->will(
            $this->returnValue(TESTS_TEMP_DIR . '/lib')
        );

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false);

        $config = $this->_getConfigStub();
        $configData = array('font_code' => array('label' => 'Label', 'path' => 'path/to/fixture.ttf'));

        $config->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            'captcha/fonts',
            'default'
        )->will(
            $this->returnValue($configData)
        );

        $object = $this->_getHelper($this->_getStoreStub(), $config, $factoryMock);
        $fonts = $object->getFonts();
        $this->assertArrayHasKey('font_code', $fonts);
        // fixture
        $this->assertArrayHasKey('label', $fonts['font_code']);
        $this->assertArrayHasKey('path', $fonts['font_code']);
        $this->assertEquals('Label', $fonts['font_code']['label']);
        $this->assertStringStartsWith(TESTS_TEMP_DIR, $fonts['font_code']['path']);
        $this->assertStringEndsWith('path/to/fixture.ttf', $fonts['font_code']['path']);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getImgDir
     * @covers \Magento\Captcha\Helper\Data::getImgDir
     */
    public function testGetImgDir()
    {
        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false);

        $dirWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array('changePermissions', 'create', 'getAbsolutePath'),
            array(),
            '',
            false
        );

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            \Magento\Framework\App\Filesystem::MEDIA_DIR
        )->will(
            $this->returnValue($dirWriteMock)
        );

        $dirWriteMock->expects(
            $this->once()
        )->method(
            'getAbsolutePath'
        )->with(
            '/captcha/base'
        )->will(
            $this->returnValue(TESTS_TEMP_DIR . '/captcha/base')
        );

        $object = $this->_getHelper($this->_getStoreStub(), $this->_getConfigStub(), $factoryMock);
        $this->assertFileNotExists(TESTS_TEMP_DIR . '/captcha');
        $result = $object->getImgDir();
        $this->assertStringStartsWith(TESTS_TEMP_DIR, $result);
        $this->assertStringEndsWith('captcha/base/', $result);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getImgUrl
     * @covers \Magento\Captcha\Helper\Data::getImgUrl
     */
    public function testGetImgUrl()
    {
        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', array(), array(), '', false);
        $object = $this->_getHelper($this->_getStoreStub(), $this->_getConfigStub(), $factoryMock);
        $this->assertEquals($object->getImgUrl(), 'http://localhost/pub/media/captcha/base/');
    }

    /**
     * Create Config Stub
     *
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected function _getConfigStub()
    {
        $config = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        return $config;
    }

    /**
     * Create Website Stub
     *
     * @return \Magento\Store\Model\Website
     */
    protected function _getWebsiteStub()
    {
        $website = $this->getMock('Magento\Store\Model\Website', array('getCode', '__wakeup'), array(), '', false);

        $website->expects($this->any())->method('getCode')->will($this->returnValue('base'));

        return $website;
    }

    /**
     * Create store stub
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);

        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/pub/media/'));

        return $store;
    }
}
