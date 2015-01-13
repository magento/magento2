<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    protected function setUp()
    {
        $this->_filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
    }

    protected function _getHelper($store, $config, $factory)
    {
        $storeManager = $this->getMockBuilder(
            'Magento\Store\Model\StoreManager'
        )->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($this->_getWebsiteStub()));
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);

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

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false);
        $factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Zend')
        )->will(
            $this->returnValue(
                new \Magento\Captcha\Model\DefaultModel(
                    $this->getMock('Magento\Framework\Session\SessionManager', [], [], '', false),
                    $this->getMock('Magento\Captcha\Helper\Data', [], [], '', false),
                    $this->getMock('Magento\Captcha\Model\Resource\LogFactory', [], [], '', false),
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

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false);
        $object = $this->_getHelper($store, $config, $factoryMock);
        $object->getConfig('enable');
    }

    public function testGetFonts()
    {
        $fontPath = 'path/to/fixture.ttf';
        $expectedFontPath = 'lib/' . $fontPath;

        $libDirMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $libDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($fontPath)
            ->will($this->returnValue($expectedFontPath));
        $this->_filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::LIB_INTERNAL)
            ->will($this->returnValue($libDirMock));

        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false);

        $config = $this->_getConfigStub();
        $configData = ['font_code' => ['label' => 'Label', 'path' => $fontPath]];

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
        $this->assertEquals($expectedFontPath, $fonts['font_code']['path']);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getImgDir
     * @covers \Magento\Captcha\Helper\Data::getImgDir
     */
    public function testGetImgDir()
    {
        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false);

        $dirWriteMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            ['changePermissions', 'create', 'getAbsolutePath'],
            [],
            '',
            false
        );

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
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
        $factoryMock = $this->getMock('Magento\Captcha\Model\CaptchaFactory', [], [], '', false);
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
        $website = $this->getMock('Magento\Store\Model\Website', ['getCode', '__wakeup'], [], '', false);

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
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/pub/media/'));

        return $store;
    }
}
