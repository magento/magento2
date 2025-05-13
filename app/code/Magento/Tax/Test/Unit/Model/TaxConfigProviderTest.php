<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxConfigProviderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $taxHelperMock;

    /**
     * @var MockObject
     */
    protected $taxConfigMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var TaxConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $this->taxHelperMock = $this->createMock(Data::class);
        $this->taxConfigMock = $this->createMock(Config::class);
        $this->checkoutSessionMock = $this->createMock(Session::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->model = new TaxConfigProvider(
            $this->taxHelperMock,
            $this->taxConfigMock,
            $this->checkoutSessionMock,
            $this->scopeConfigMock
        );
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param array $expectedResult
     * @param int $cartShippingBoth
     * @param int $cartShippingExclTax
     * @param int $cartBothPrices
     * @param int $cartPriceExclTax
     * @param int $cartSubTotalBoth
     * @param int $cartSubTotalExclTax
     * @param string|null $calculationType
     * @param bool $isQuoteVirtual
     */
    public function testGetConfig(
        $expectedResult,
        $cartShippingBoth,
        $cartShippingExclTax,
        $cartBothPrices,
        $cartPriceExclTax,
        $cartSubTotalBoth,
        $cartSubTotalExclTax,
        $isQuoteVirtual,
        $config
    ) {
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingBoth')
            ->willReturn($cartShippingBoth);
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingExclTax')
            ->willReturn($cartShippingExclTax);

        $this->taxHelperMock->expects($this->any())->method('displayCartBothPrices')
            ->willReturn($cartBothPrices);
        $this->taxHelperMock->expects($this->any())->method('displayCartPriceExclTax')
            ->willReturn($cartPriceExclTax);

        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalBoth')
            ->willReturn($cartSubTotalBoth);
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalExclTax')
            ->willReturn($cartSubTotalExclTax);

        $this->taxHelperMock->expects(($this->any()))->method('displayShippingPriceExcludingTax')
            ->willReturn(1);
        $this->taxHelperMock->expects(($this->any()))->method('displayShippingBothPrices')
            ->willReturn(1);
        $this->taxHelperMock->expects(($this->any()))->method('displayFullSummary')
            ->willReturn(1);
        $this->taxConfigMock->expects(($this->any()))->method('displayCartTaxWithGrandTotal')
            ->willReturn(1);
        $this->taxConfigMock->expects(($this->any()))->method('displayCartZeroTax')
            ->willReturn(1);

        $valueMap = [];
        foreach ($config as $key => $value) {
            $valueMap[] = [$key, ScopeInterface::SCOPE_STORE, null, $value];
        }
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap($valueMap);
        $this->quoteMock->expects($this->any())->method('isVirtual')->willReturn($isQuoteVirtual);
        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getConfigDataProvider()
    {
        return [
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 1,
                'isQuoteVirtual' => false,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'shipping',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'excluding',
                    'reviewTotalsDisplayMode' => 'excluding',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 1,
                'isQuoteVirtual' => false,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'billing',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0,
                'isQuoteVirtual' => true,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'shipping',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => true,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0,
                'isQuoteVirtual' => true,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'billing',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0,
                'isQuoteVirtual' => false,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'shipping',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => 12,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0,
                'isQuoteVirtual' => false,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'shipping',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 12,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
            'zeroRegionToNull' => [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1,
                    'reloadOnBillingAddress' => false,
                    'defaultCountryId' => 'US',
                    'defaultRegionId' => null,
                    'defaultPostcode' => '*',
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0,
                'isQuoteVirtual' => false,
                'config' => [
                    Config::CONFIG_XML_PATH_BASED_ON => 'shipping',
                    Config::CONFIG_XML_PATH_DEFAULT_COUNTRY => 'US',
                    Config::CONFIG_XML_PATH_DEFAULT_REGION => 0,
                    Config::CONFIG_XML_PATH_DEFAULT_POSTCODE => '*',
                ],
            ],
        ];
    }
}
