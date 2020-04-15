<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\System\Message\Notification;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notification\RoundingErrors as RoundingErrorsNotification;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notification\RoundingErrors
 */
class RoundingErrorsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RoundingErrorsNotification
     */
    private $roundingErrorsNotification;

    /**
     * @var StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig | \PHPUnit\Framework\MockObject\MockObject
     */
    private $taxConfigMock;

    protected function setUp(): void
    {
        parent::setUp();

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getName')->willReturn('testWebsiteName');
        $storeMock = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getWebsite', 'getName']
        );
        $storeMock->expects($this->any())->method('getName')->willReturn('testStoreName');
        $storeMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->taxConfigMock = $this->createMock(TaxConfig::class);
        $this->roundingErrorsNotification = (new ObjectManager($this))->getObject(
            RoundingErrorsNotification::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    public function testIsDisplayedNotDisplayedUnitBased()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongDisplaySettingsIgnored')->willReturn(false);

        $this->taxConfigMock->expects($this->any())
            ->method('getAlgorithm')->willReturn(\Magento\Tax\Model\Calculation::CALC_UNIT_BASE);

        $this->taxConfigMock->expects($this->any())
            ->method('getPriceDisplayType')->willReturn(\Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX);
        $this->taxConfigMock->expects($this->any())
            ->method('getShippingPriceDisplayType')->willReturn(\Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX);

        $this->taxConfigMock->expects($this->any())->method('displayCartPricesBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displaySalesPricesBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displaySalesSubtotalBoth')->willReturn(false);

        $this->taxConfigMock->expects($this->any())->method('displaySalesShippingBoth')->willReturn(true);

        $this->assertFalse($this->roundingErrorsNotification->isDisplayed());
    }

    public function testIsDisplayedNotDisplayed()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongDisplaySettingsIgnored')->willReturn(false);

        $this->taxConfigMock->expects($this->any())
            ->method('getAlgorithm')->willReturn(\Magento\Tax\Model\Calculation::CALC_ROW_BASE);

        $this->taxConfigMock->expects($this->any())
            ->method('getPriceDisplayType')->willReturn(\Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX);
        $this->taxConfigMock->expects($this->any())
            ->method('getShippingPriceDisplayType')->willReturn(\Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX);

        $this->taxConfigMock->expects($this->any())->method('displayCartPricesBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displaySalesPricesBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displaySalesSubtotalBoth')->willReturn(false);
        $this->taxConfigMock->expects($this->any())->method('displaySalesShippingBoth')->willReturn(false);

        $this->assertFalse($this->roundingErrorsNotification->isDisplayed());
    }

    public function testIsDisplayedIgnoreWrongConfiguration()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongDisplaySettingsIgnored')->willReturn(true);
        $this->assertFalse($this->roundingErrorsNotification->isDisplayed());
    }

    public function testGetText()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongDisplaySettingsIgnored')->willReturn(false);

        $this->taxConfigMock->expects($this->any())->method('displaySalesShippingBoth')->willReturn(true);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('tax/tax/ignoreTaxNotification', ['section' => 'price_display'])
            ->willReturn('http://example.com');
        $this->roundingErrorsNotification->isDisplayed();
        $this->assertEquals(
            '<strong>Your current tax configuration may result in rounding errors. '
            . '</strong><p>Store(s) affected: testWebsiteName (testStoreName)</p><p>Click on the link to '
            . '<a href="http://example.com">ignore this notification</a></p>',
            $this->roundingErrorsNotification->getText()
        );
    }
}
