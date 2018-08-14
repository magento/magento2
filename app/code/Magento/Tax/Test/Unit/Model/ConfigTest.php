<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\Tax\Model\Config
 */
namespace Magento\Tax\Test\Unit\Model;

use \Magento\Tax\Model\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the setter/getter methods that bypass the ScopeConfigInterface object
     *
     * @param string $setterMethod
     * @param string $getterMethod
     * @param bool $value
     * @dataProvider dataProviderDirectSettersGettersMethods
     */
    public function testDirectSettersGettersMethods($setterMethod, $getterMethod, $value)
    {
        // Need a mocked object with only dummy methods.  It is just needed for construction.
        // The setter/getter methods do not use this object (for this set of tests).
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        /** @var \Magento\Tax\Model\Config */
        $model = new Config($scopeConfigMock);
        $model->{$setterMethod}($value);
        $this->assertEquals($value, $model->{$getterMethod}());
    }

    /**
     * @return array
     */
    public function dataProviderDirectSettersGettersMethods()
    {
        return [
            ['setShippingPriceIncludeTax', 'shippingPriceIncludesTax', true],
            ['setShippingPriceIncludeTax', 'shippingPriceIncludesTax', false],
            ['setNeedUseShippingExcludeTax', 'getNeedUseShippingExcludeTax', true],
            ['setNeedUseShippingExcludeTax', 'getNeedUseShippingExcludeTax', false],
            ['setPriceIncludesTax', 'priceIncludesTax', true],
            ['setPriceIncludesTax', 'priceIncludesTax', false],
            ['setPriceIncludesTax', 'priceIncludesTax', null]
        ];
    }

    /**
     * Tests the getCalculationSequence method
     *
     * @param bool $applyTaxAfterDiscount
     * @param bool $discountTaxIncl
     * @param string $expectedValue
     * @dataProvider dataProviderGetCalculationSequence
     */
    public function testGetCalculationSequence($applyTaxAfterDiscount, $discountTaxIncl, $expectedValue)
    {
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfigMock->expects(
            $this->at(0))->method('getValue')->will($this->returnValue($applyTaxAfterDiscount));
        $scopeConfigMock->expects(
            $this->at(1))->method('getValue')->will($this->returnValue($discountTaxIncl));

        /** @var \Magento\Tax\Model\Config */
        $model = new Config($scopeConfigMock);
        $this->assertEquals($expectedValue, $model->getCalculationSequence());
    }

    /**
     * @return array
     */
    public function dataProviderGetCalculationSequence()
    {
        return [
            [true,  true,  \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL],
            [true,  false, \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL],
            [false, true,  \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL],
            [false, false, \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL]
        ];
    }

    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     *
     * @param string $method
     * @param string $path
     * @param bool|int $configValue
     * @param bool $expectedValue
     * @dataProvider dataProviderScopeConfigMethods
     */
    public function testScopeConfigMethods($method, $path, $configValue, $expectedValue)
    {
        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue($configValue));

        /** @var \Magento\Tax\Model\Config */
        $model = new Config($scopeConfigMock);
        $this->assertEquals($expectedValue, $model->{$method}());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderScopeConfigMethods()
    {
        return [
            [
                'priceIncludesTax',
                Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                true,
                true,
            ],
            [
                'applyTaxAfterDiscount',
                Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT,
                true,
                true
            ],
            [
                'getPriceDisplayType',
                Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                true,
                true
            ],
            [
                'discountTax',
                Config::CONFIG_XML_PATH_DISCOUNT_TAX,
                1,
                true
            ],
            [
                'getAlgorithm',
                Config::XML_PATH_ALGORITHM,
                true,
                true
            ],
            [
                'getShippingTaxClass',
                Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
                true,
                true
            ],
            [
                'getShippingPriceDisplayType',
                Config::CONFIG_XML_PATH_DISPLAY_SHIPPING,
                true,
                true
            ],
            [
                'shippingPriceIncludesTax',
                Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX,
                true,
                true
            ],
            [
                'displayCartPricesInclTax',
                Config::XML_PATH_DISPLAY_CART_PRICE,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displayCartPricesExclTax',
                Config::XML_PATH_DISPLAY_CART_PRICE,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displayCartPricesBoth',
                Config::XML_PATH_DISPLAY_CART_PRICE,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displayCartSubtotalInclTax',
                Config::XML_PATH_DISPLAY_CART_SUBTOTAL,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displayCartSubtotalExclTax',
                Config::XML_PATH_DISPLAY_CART_SUBTOTAL,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displayCartSubtotalBoth',
                Config::XML_PATH_DISPLAY_CART_SUBTOTAL,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displayCartShippingInclTax',
                Config::XML_PATH_DISPLAY_CART_SHIPPING,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displayCartShippingExclTax',
                Config::XML_PATH_DISPLAY_CART_SHIPPING,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displayCartShippingBoth',
                Config::XML_PATH_DISPLAY_CART_SHIPPING,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displayCartDiscountInclTax',
                Config::XML_PATH_DISPLAY_CART_DISCOUNT,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displayCartDiscountExclTax',
                Config::XML_PATH_DISPLAY_CART_DISCOUNT,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displayCartDiscountBoth',
                Config::XML_PATH_DISPLAY_CART_DISCOUNT,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displayCartTaxWithGrandTotal',
                Config::XML_PATH_DISPLAY_CART_GRANDTOTAL,
                true,
                true
            ],
            [
                'displayCartFullSummary',
                Config::XML_PATH_DISPLAY_CART_FULL_SUMMARY,
                true,
                true
            ],
            [
                'displayCartZeroTax',
                Config::XML_PATH_DISPLAY_CART_ZERO_TAX,
                true,
                true
            ],
            [
                'displaySalesPricesInclTax',
                Config::XML_PATH_DISPLAY_SALES_PRICE,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displaySalesPricesExclTax',
                Config::XML_PATH_DISPLAY_SALES_PRICE,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displaySalesPricesBoth',
                Config::XML_PATH_DISPLAY_SALES_PRICE,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displaySalesSubtotalInclTax',
                Config::XML_PATH_DISPLAY_SALES_SUBTOTAL,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displaySalesSubtotalExclTax',
                Config::XML_PATH_DISPLAY_SALES_SUBTOTAL,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displaySalesSubtotalBoth',
                Config::XML_PATH_DISPLAY_SALES_SUBTOTAL,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displaySalesShippingInclTax',
                Config::XML_PATH_DISPLAY_SALES_SHIPPING,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displaySalesShippingExclTax',
                Config::XML_PATH_DISPLAY_SALES_SHIPPING,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displaySalesShippingBoth',
                Config::XML_PATH_DISPLAY_SALES_SHIPPING,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displaySalesDiscountInclTax',
                Config::XML_PATH_DISPLAY_SALES_DISCOUNT,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                true
            ],
            [
                'displaySalesDiscountExclTax',
                Config::XML_PATH_DISPLAY_SALES_DISCOUNT,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                true
            ],
            [
                'displaySalesDiscountBoth',
                Config::XML_PATH_DISPLAY_SALES_DISCOUNT,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
            [
                'displaySalesTaxWithGrandTotal',
                Config::XML_PATH_DISPLAY_SALES_GRANDTOTAL,
                true,
                true
            ],
            [
                'displaySalesFullSummary',
                Config::XML_PATH_DISPLAY_SALES_FULL_SUMMARY,
                true,
                true
            ],
            [
                'displaySalesZeroTax',
                Config::XML_PATH_DISPLAY_SALES_ZERO_TAX,
                true,
                true
            ],
            [
                'crossBorderTradeEnabled',
                Config::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED,
                true,
                true
            ],
            [
                'isWrongDisplaySettingsIgnored',
                Config::XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_DISPLAY,
                true,
                true
            ],
            [
                'isWrongDiscountSettingsIgnored',
                Config::XML_PATH_TAX_NOTIFICATION_IGNORE_DISCOUNT,
                true,
                true
            ],
            [
                'isWrongApplyDiscountSettingIgnored',
                Config::XML_PATH_TAX_NOTIFICATION_IGNORE_APPLY_DISCOUNT,
                true,
                true
            ],
            [
                'getInfoUrl',
                Config::XML_PATH_TAX_NOTIFICATION_INFO_URL,
                'http:\\kiwis.rule.com',
                'http:\\kiwis.rule.com'
            ]
        ];
    }
}
