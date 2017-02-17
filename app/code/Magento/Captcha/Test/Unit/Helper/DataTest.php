<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Captcha\Model\CaptchaFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\Captcha\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var \Magento\Framework\App\Helper\Context $context */
        $context = $arguments['context'];
        $this->configMock = $context->getScopeConfig();
        $this->_filesystem = $arguments['filesystem'];
        $storeManager = $arguments['storeManager'];
        $storeManager->expects($this->any())->method('getWebsite')->will($this->returnValue($this->_getWebsiteStub()));
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->_getStoreStub()));
        $this->factoryMock = $arguments['factory'];
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @covers \Magento\Captcha\Helper\Data::getCaptcha
     */
    public function testGetCaptcha()
    {
        if (!function_exists("imageftbbox")) {
            $this->markTestSkipped('imageftbbox is not available on the test environment');
        }

        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'customer/captcha/type'
        )->will(
            $this->returnValue('zend')
        );

        $this->factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Zend')
        )->will(
            $this->returnValue(
                new \Magento\Captcha\Model\DefaultModel(
                    $this->getMock(\Magento\Framework\Session\SessionManager::class, [], [], '', false),
                    $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false),
                    $this->getMock(\Magento\Captcha\Model\ResourceModel\LogFactory::class, ['create'], [], '', false),
                    'user_create'
                )
            )
        );

        $this->assertInstanceOf(\Magento\Captcha\Model\DefaultModel::class, $this->helper->getCaptcha('user_create'));
    }

    /**
     * @covers \Magento\Captcha\Helper\Data::getConfig
     */
    public function testGetConfigNode()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'customer/captcha/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue('1')
        );

        $this->helper->getConfig('enable');
    }

    public function testGetFonts()
    {
        $fontPath = 'path/to/fixture.ttf';
        $expectedFontPath = 'lib/' . $fontPath;

        $libDirMock = $this->getMock(\Magento\Framework\Filesystem\Directory\Read::class, [], [], '', false);
        $libDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($fontPath)
            ->will($this->returnValue($expectedFontPath));
        $this->_filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::LIB_INTERNAL)
            ->will($this->returnValue($libDirMock));

        $configData = ['font_code' => ['label' => 'Label', 'path' => $fontPath]];

        $this->configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            'captcha/fonts',
            'default'
        )->will(
            $this->returnValue($configData)
        );

        $fonts = $this->helper->getFonts();
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
        $dirWriteMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Write::class,
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

        $this->assertFileNotExists(TESTS_TEMP_DIR . '/captcha');
        $result = $this->helper->getImgDir();
        $this->assertStringStartsWith(TESTS_TEMP_DIR, $result);
        $this->assertStringEndsWith('captcha/base/', $result);
    }

    /**
     * @covers \Magento\Captcha\Model\DefaultModel::getImgUrl
     * @covers \Magento\Captcha\Helper\Data::getImgUrl
     */
    public function testGetImgUrl()
    {
        $this->assertEquals($this->helper->getImgUrl(), 'http://localhost/pub/media/captcha/base/');
    }

    /**
     * Create Website Stub
     *
     * @return \Magento\Store\Model\Website
     */
    protected function _getWebsiteStub()
    {
        $website = $this->getMock(\Magento\Store\Model\Website::class, ['getCode', '__wakeup'], [], '', false);

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
        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);

        $store->expects($this->any())->method('getBaseUrl')->will($this->returnValue('http://localhost/pub/media/'));

        return $store;
    }
}
