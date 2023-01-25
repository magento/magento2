<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\ProductAlert\Block\Email\Price;
use Magento\ProductAlert\Helper\Data as HelperData;
use Magento\ProductAlert\Model\Observer;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $encoderMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * Setup environment for testing
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->encoderMock = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $this->contextMock->expects($this->once())->method('getUrlEncoder')->willReturn($this->encoderMock);
        $this->contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())->method('getId')->willReturn(1);

        $this->helper = $this->objectManagerHelper->getObject(
            HelperData::class,
            [
                'context' => $this->contextMock,
                'layout' => $this->layoutMock
            ]
        );
        $this->helper->setProduct($productMock);
    }

    /**
     * Test getSaveUrl() function
     */
    public function testGetSaveUrl()
    {
        $currentUrl = 'http://www.example.com/';
        $type = 'stock';
        $uenc = strtr(base64_encode($currentUrl), '+/=', '-_,');
        $expected = 'http://www.example.com/roductalert/add/stock/product_id/1/uenc/' . $uenc;

        $this->urlBuilderMock->expects($this->any())->method('getCurrentUrl')->willReturn($currentUrl);
        $this->encoderMock->expects($this->any())->method('encode')
            ->with($currentUrl)
            ->willReturn($uenc);
        $this->urlBuilderMock->expects($this->any())->method('getUrl')
            ->with(
                'productalert/add/' . $type,
                [
                    'product_id' => 1,
                    'uenc' => $uenc
                ]
            )
            ->willReturn($expected);

        $this->assertEquals($expected, $this->helper->getSaveUrl($type));
    }

    /**
     * Test createBlock() with no exception
     */
    public function testCreateBlockWithNoException()
    {
        $priceBlockMock = $this->createMock(Price::class);
        $this->layoutMock->expects($this->once())->method('createBlock')->willReturn($priceBlockMock);

        $this->assertEquals($priceBlockMock, $this->helper->createBlock(Price::class));
    }

    /**
     * Test createBlock() with exception
     */
    public function testCreateBlockWithException()
    {
        $invalidBlock = $this->createMock(Product::class);
        $this->expectException(LocalizedException::class);

        $this->helper->createBlock($invalidBlock);
    }

    /**
     * Test isStockAlertAllowed() function with Yes settings
     */
    public function testIsStockAlertAllowedWithYesSettings()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')
            ->with(Observer::XML_PATH_STOCK_ALLOW, ScopeInterface::SCOPE_STORE)
            ->willReturn('1');

        $this->assertEquals('1', $this->helper->isStockAlertAllowed());
    }

    /**
     * Test isPriceAlertAllowed() function with Yes settings
     */
    public function testIsPriceAlertAllowedWithYesSetting()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')
            ->with(Observer::XML_PATH_PRICE_ALLOW, ScopeInterface::SCOPE_STORE)
            ->willReturn('1');

        $this->assertEquals('1', $this->helper->isPriceAlertAllowed());
    }
}
