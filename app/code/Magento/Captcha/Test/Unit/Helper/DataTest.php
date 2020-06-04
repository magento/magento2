<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Helper;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\CaptchaFactory;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_filesystem;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var CaptchaFactory|MockObject
     */
    protected $factoryMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $className = Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /** @var Context $context */
        $context = $arguments['context'];
        $this->configMock = $context->getScopeConfig();
        $this->_filesystem = $arguments['filesystem'];
        $storeManager = $arguments['storeManager'];
        $storeManager->expects($this->any())->method('getWebsite')->willReturn($this->_getWebsiteStub());
        $storeManager->expects($this->any())->method('getStore')->willReturn($this->_getStoreStub());
        $this->factoryMock = $arguments['factory'];
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @covers \Magento\Captcha\Helper\Data::getCaptcha
     */
    public function testGetCaptcha()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            'customer/captcha/type'
        )->willReturn(
            'zend'
        );

        $this->factoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Zend'
        )->willReturn(
            new DefaultModel(
                $this->createMock(SessionManager::class),
                $this->createMock(Data::class),
                $this->createPartialMock(LogFactory::class, ['create']),
                'user_create'
            )
        );

        $this->assertInstanceOf(DefaultModel::class, $this->helper->getCaptcha('user_create'));
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
            ScopeInterface::SCOPE_STORE
        )->willReturn(
            '1'
        );

        $this->helper->getConfig('enable');
    }

    public function testGetFonts()
    {
        $fontPath = 'path/to/fixture.ttf';
        $expectedFontPath = 'lib/' . $fontPath;

        $libDirMock = $this->createMock(Read::class);
        $libDirMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($fontPath)
            ->willReturn($expectedFontPath);
        $this->_filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::LIB_INTERNAL)
            ->willReturn($libDirMock);

        $configData = ['font_code' => ['label' => 'Label', 'path' => $fontPath]];

        $this->configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            'captcha/fonts',
            'default'
        )->willReturn(
            $configData
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
        $dirWriteMock = $this->createPartialMock(
            Write::class,
            ['changePermissions', 'create', 'getAbsolutePath']
        );

        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            DirectoryList::MEDIA
        )->willReturn(
            $dirWriteMock
        );

        $dirWriteMock->expects(
            $this->once()
        )->method(
            'getAbsolutePath'
        )->with(
            '/captcha/base'
        )->willReturn(
            TESTS_TEMP_DIR . '/captcha/base'
        );

        $this->assertFileDoesNotExist(TESTS_TEMP_DIR . '/captcha');
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
        $website = $this->createPartialMock(Website::class, ['getCode', '__wakeup']);

        $website->expects($this->any())->method('getCode')->willReturn('base');

        return $website;
    }

    /**
     * Create store stub
     *
     * @return Store
     */
    protected function _getStoreStub()
    {
        $store = $this->createMock(Store::class);

        $store->expects($this->any())->method('getBaseUrl')->willReturn('http://localhost/pub/media/');

        return $store;
    }
}
