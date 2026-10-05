<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Tax\Model\Config
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * Tests the setter/getter methods that bypass the ScopeConfigInterface object
     */
    #[DataProvider('dataProviderDirectSettersGettersMethods')]
    public function testDirectSettersGettersMethods(string $setterMethod, string $getterMethod, $value): void
    {
        // Need a mocked object with only dummy methods.  It is just needed for construction.
        // The setter/getter methods do not use this object (for this set of tests).
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        /** @var Config */
        $model = new Config($scopeConfigMock);
        $model->{$setterMethod}($value);
        $this->assertEquals($value, $model->{$getterMethod}());
    }

    /**
     * @return array
     */
    public static function dataProviderDirectSettersGettersMethods(): array
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
     */
    #[DataProvider('dataProviderGetCalculationSequence')]
    public function testGetCalculationSequence(
        bool $applyTaxAfterDiscount,
        bool $discountTaxIncl,
        string $expectedValue
    ): void {
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $scopeConfigMock
            ->method('getValue')
            ->willReturnOnConsecutiveCalls($applyTaxAfterDiscount, $discountTaxIncl);

        /** @var Config */
        $model = new Config($scopeConfigMock);
        $this->assertEquals($expectedValue, $model->getCalculationSequence());
    }

    /**
     * @return array
     */
    public static function dataProviderGetCalculationSequence(): array
    {
        return [
            [true,  true,  Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL],
            [true,  false, Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL],
            [false, true,  Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL],
            [false, false, Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL]
        ];
    }

    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     */
    #[DataProvider('dataProviderScopeConfigMethods')]
    public function testScopeConfigMethods(string $method, string $path, $configValue, $expectedValue): void
    {
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with($path, ScopeInterface::SCOPE_STORE, null)
            ->willReturn($configValue);

        /** @var Config */
        $model = new Config($scopeConfigMock);
        $this->assertEquals($expectedValue, $model->{$method}());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataProviderScopeConfigMethods(): array
    {
        return [
            [
                'priceIncludesTax',
                Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                true,
                true
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

    #[DataProvider('needPriceConversionDataProvider')]
    public function testNeedPriceConversion(
        bool $catalogIncludesTax,
        int $catalogDisplay,
        int $cartDisplay,
        bool $shippingIncludesTax,
        int $shippingDisplay,
        int|bool $expected,
        bool $useShippingExcludeTax = false
    ): void {
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, ScopeInterface::SCOPE_STORE, null, $catalogIncludesTax],
                [Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, ScopeInterface::SCOPE_STORE, null, $catalogDisplay],
                [Config::XML_PATH_DISPLAY_CART_PRICE, ScopeInterface::SCOPE_STORE, null, $cartDisplay],
                [Config::XML_PATH_DISPLAY_CART_SHIPPING, ScopeInterface::SCOPE_STORE, null, $shippingDisplay],
            ]);
        $model = new Config($scopeConfigMock);
        $model->setNeedUseShippingExcludeTax($useShippingExcludeTax);
        $model->setShippingPriceIncludeTax($shippingIncludesTax);
        $this->assertSame($expected, $model->needPriceConversion());
    }

    public static function needPriceConversionDataProvider(): array
    {
        $excluding = Config::DISPLAY_TYPE_EXCLUDING_TAX;
        $including = Config::DISPLAY_TYPE_INCLUDING_TAX;
        $both = Config::DISPLAY_TYPE_BOTH;
        $plus = Config::PRICE_CONVERSION_PLUS;
        $minus = Config::PRICE_CONVERSION_MINUS;
        $catalogCases = [
            [false, $excluding, $excluding, false],
            [false, $excluding, $including, false],
            [false, $excluding, $both, true],
            [false, $including, $excluding, $plus],
            [false, $including, $including, $plus],
            [false, $including, $both, $plus],
            [false, $both, $excluding, $plus],
            [false, $both, $including, $plus],
            [false, $both, $both, $plus],
            [true, $excluding, $excluding, $minus],
            [true, $excluding, $including, $minus],
            [true, $excluding, $both, $minus],
            [true, $including, $excluding, false],
            [true, $including, $including, true],
            [true, $including, $both, true],
            [true, $both, $excluding, $minus],
            [true, $both, $including, $minus],
            [true, $both, $both, $minus],
        ];
        $cases = [];
        foreach ($catalogCases as [$catalogIncludesTax, $catalogDisplay, $cartDisplay, $expected]) {
            foreach ([false, true] as $shippingIncludesTax) {
                foreach ([
                    Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    Config::DISPLAY_TYPE_INCLUDING_TAX,
                    Config::DISPLAY_TYPE_BOTH,
                ] as $shippingDisplay) {
                    $name = sprintf(
                        'catalog input %d display %d cart %d shipping input %d display %d',
                        $catalogIncludesTax,
                        $catalogDisplay,
                        $cartDisplay,
                        $shippingIncludesTax,
                        $shippingDisplay
                    );
                    $cases[$name] = [
                        $catalogIncludesTax,
                        $catalogDisplay,
                        $cartDisplay,
                        $shippingIncludesTax,
                        $shippingDisplay,
                        $expected,
                    ];
                }
            }
        }
        foreach ($catalogCases as [$catalogIncludesTax, $catalogDisplay, $cartDisplay, $expected]) {
            if ($catalogIncludesTax) {
                $cases['legacy flag display ' . $catalogDisplay . ' cart ' . $cartDisplay] = [
                    false,
                    $catalogDisplay,
                    $cartDisplay,
                    false,
                    Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    $expected,
                    true,
                ];
            }
        }
        return $cases;
    }
}
