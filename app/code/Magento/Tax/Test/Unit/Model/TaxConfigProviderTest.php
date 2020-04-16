<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use \Magento\Tax\Model\Config;

class TaxConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Tax\Model\TaxConfigProvider
     */
    protected $model;

    protected function setUp(): void
    {
        $this->taxHelperMock = $this->createMock(\Magento\Tax\Helper\Data::class);
        $this->taxConfigMock = $this->createMock(\Magento\Tax\Model\Config::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->model = new \Magento\Tax\Model\TaxConfigProvider(
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
            $valueMap[] = [$key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null, $value];
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
    public function getConfigDataProvider()
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
