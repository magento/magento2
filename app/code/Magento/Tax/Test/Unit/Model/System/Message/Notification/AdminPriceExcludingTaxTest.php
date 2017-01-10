<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Test\Unit\Model\System\Message\Notification;

use Magento\Tax\Model\Config as TaxConfig;
use Magento\Tax\Model\System\Message\Notification\AdminPriceExcludingTax as AdminPriceExcludingTaxNotification;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Test class for @see \Magento\Tax\Model\System\Message\Notification\AdminPriceExcludingTax
 *
 * @SuppressWarnings(PHPMD)
 */
class AdminPriceExcludingTaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdminPriceExcludingTaxNotification
     */
    private $adminPriceExcludingTaxNotification;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var TaxConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    protected function setUp()
    {
        parent::setUp();

        $websiteMock = $this->getMock(WebsiteInterface::class, [], [], '', false);
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
        $this->storeManagerMock = $this->getMock(StoreManagerInterface::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStores')->willReturn([$storeMock]);

        $this->urlBuilderMock = $this->getMock(UrlInterface::class, [], [], '', false);
        $this->taxConfigMock = $this->getMock(TaxConfig::class, [], [], '', false);
        $this->adminPriceExcludingTaxNotification = (new ObjectManager($this))->getObject(
            AdminPriceExcludingTaxNotification::class,
            [
                'storeManager' => $this->storeManagerMock,
                'urlBuilder' => $this->urlBuilderMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    /**
     * @dataProvider dataProviderIsDisplayed
     */
    public function testIsDisplayed(
        $isWrongPriceExcludingTaxSettingsIgnored,
        $priceIncludesTax,
        $shippingPriceIncludesTax,
        $getPriceDisplayType,
        $getShippingPriceDisplayType,
        $displayCartPricesExclTax,
        $displayCartSubtotalExclTax,
        $displayCartShippingExclTax,
        $displaySalesPricesExclTax,
        $displaySalesSubtotalExclTax,
        $displaySalesShippingExclTax,
        $expectedResult
    ) {
        $this->taxConfigMock->expects($this->any())
            ->method('isWrongPriceExcludingTaxSettingsIgnored')->willReturn($isWrongPriceExcludingTaxSettingsIgnored);

        $this->taxConfigMock->expects($this->any())->method('priceIncludesTax')->willReturn($priceIncludesTax);
        $this->taxConfigMock->expects($this->any())->method('shippingPriceIncludesTax')
            ->willReturn($shippingPriceIncludesTax);

        $this->taxConfigMock->expects($this->any())
            ->method('getPriceDisplayType')->willReturn($getPriceDisplayType);
        $this->taxConfigMock->expects($this->any())
            ->method('getShippingPriceDisplayType')->willReturn($getShippingPriceDisplayType);

        $this->taxConfigMock->expects($this->any())->method('displayCartPricesExclTax')
            ->willReturn($displayCartPricesExclTax);
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalExclTax')
            ->willReturn($displayCartSubtotalExclTax);
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingExclTax')
            ->willReturn($displayCartShippingExclTax);
        $this->taxConfigMock->expects($this->any())->method('displaySalesPricesExclTax')
            ->willReturn($displaySalesPricesExclTax);
        $this->taxConfigMock->expects($this->any())->method('displaySalesSubtotalExclTax')
            ->willReturn($displaySalesSubtotalExclTax);
        $this->taxConfigMock->expects($this->any())->method('displaySalesShippingExclTax')
            ->willReturn($displaySalesShippingExclTax);

        $this->assertEquals($expectedResult, $this->adminPriceExcludingTaxNotification->isDisplayed());
    }

    public function dataProviderIsDisplayed()
    {
        return [
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                true, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                false, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                true, // $priceIncludesTax
                true, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH, // $getShippingPriceDisplayType,
                false, // $displayCartPricesExclTax,
                false, // $displayCartSubtotalExclTax,
                false, // $displayCartShippingExclTax,
                false, // $displaySalesPricesExclTax,
                false, // $displaySalesSubtotalExclTax,
                false, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                true, // $priceIncludesTax
                true, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                false, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                true, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                true, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                false // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                false, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                false, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                false, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                false, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                false, // $displaySalesSubtotalExclTax,
                true, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
            [
                false, // $isWrongPriceExcludingTaxSettingsIgnored
                false, // $priceIncludesTax
                false, // $shippingPriceIncludesTax,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getPriceDisplayType,
                \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX, // $getShippingPriceDisplayType,
                true, // $displayCartPricesExclTax,
                true, // $displayCartSubtotalExclTax,
                true, // $displayCartShippingExclTax,
                true, // $displaySalesPricesExclTax,
                true, // $displaySalesSubtotalExclTax,
                false, // $displaySalesShippingExclTax,
                true // $expectedResult
            ],
        ];
    }

    public function testGetText()
    {
        $this->taxConfigMock->expects($this->any())->method('isWrongPriceExcludingTaxSettingsIgnored')
            ->willReturn(false);

        $this->taxConfigMock->expects($this->any())->method('displaySalesShippingBoth')->willReturn(true);

        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->with('tax/tax/ignoreTaxNotification', ['section' => 'price_excluding_tax'])
            ->willReturn('http://example.com');
        $this->adminPriceExcludingTaxNotification->isDisplayed();
        $this->assertEquals(
            '<strong>Current tax configuration can result in rounding errors and discount calculation errors. '
            . '</strong><p>Store(s) affected: testWebsiteName (testStoreName)</p><p>Click on the link to '
            . '<a href="http://example.com">ignore this notification</a></p>',
            $this->adminPriceExcludingTaxNotification->getText()
        );
    }
}
