<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxRuleFixtureFactory;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject under test for tax and catalog logic.
     *
     * @var \Magento\Catalog\Helper\Data
     */
    private $helper;

    /**
     * Application object manager for resolving dependencies.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Array of default tax classes ids
     *
     * Key is class name
     *
     * @var int[]
     */
    private $taxClasses;

    /**
     * Array of default tax rates ids.
     *
     * Key is rate percentage as string.
     *
     * @var int[]
     */
    private $taxRates;

    /**
     * Array of default tax rules ids.
     *
     * Key is rule code.
     *
     * @var int[]
     */
    private $taxRules;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRuleFixtureFactory;

    /**
     * @var \Magento\Framework\App\MutableScopeConfig
     */
    private $scopeConfig;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(\Magento\Catalog\Helper\Data::class);
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();
        $this->scopeConfig = $this->objectManager->get(\Magento\Framework\App\MutableScopeConfig::class);
    }

    protected function tearDown(): void
    {
        $this->tearDownDefaultRules();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetBreadcrumbPath()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $category->load(5);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_category', $category);

        try {
            $path = $this->helper->getBreadcrumbPath();
            $this->assertIsArray($path);
            $this->assertEquals(['category3', 'category4', 'category5'], array_keys($path));
            $this->assertArrayHasKey('label', $path['category3']);
            $this->assertArrayHasKey('link', $path['category3']);
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
            throw $e;
        }
    }

    public function testGetCategory()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_category', $category);
        try {
            $this->assertSame($category, $this->helper->getCategory());
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_category');
            throw $e;
        }
    }

    public function testGetProduct()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_product', $product);
        try {
            $this->assertSame($product, $this->helper->getProduct());
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_product');
        } catch (\Exception $e) {
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('current_product');
            throw $e;
        }
    }

    public function testSplitSku()
    {
        $sku = 'one-two-three';
        $this->assertEquals(['on', 'e-', 'tw', 'o-', 'th', 're', 'e'], $this->helper->splitSku($sku, 2));
    }

    public function testGetAttributeHiddenFields()
    {
        $this->assertEquals([], $this->helper->getAttributeHiddenFields());
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('attribute_type_hidden_fields', 'test');
        try {
            $this->assertEquals('test', $this->helper->getAttributeHiddenFields());
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('attribute_type_hidden_fields');
        } catch (\Exception $e) {
            $objectManager->get(\Magento\Framework\Registry::class)->unregister('attribute_type_hidden_fields');
            throw $e;
        }
    }

    public function testGetPriceScopeDefault()
    {
        // $this->assertEquals(\Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL, $this->helper->getPriceScope());
        $this->assertNull($this->helper->getPriceScope());
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testGetPriceScope()
    {
        $this->assertEquals(\Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE, $this->helper->getPriceScope());
    }

    public function testIsPriceGlobalDefault()
    {
        $this->assertTrue($this->helper->isPriceGlobal());
    }

    /**
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testIsPriceGlobal()
    {
        $this->assertFalse($this->helper->isPriceGlobal());
    }

    public function testIsUsingStaticUrlsAllowedDefault()
    {
        $this->assertFalse($this->helper->isUsingStaticUrlsAllowed());
    }

    /**
     * isUsingStaticUrlsAllowed()
     * setStoreId()
     * @magentoConfigFixture current_store cms/wysiwyg/use_static_urls_in_catalog 1
     */
    public function testIsUsingStaticUrlsAllowed()
    {
        $this->assertTrue($this->helper->isUsingStaticUrlsAllowed());
        $this->helper->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId()
        );
        $this->assertTrue($this->helper->isUsingStaticUrlsAllowed());
    }

    public function testIsUrlDirectivesParsingAllowedDefault()
    {
        $this->assertTrue($this->helper->isUrlDirectivesParsingAllowed());
    }

    /**
     * isUrlDirectivesParsingAllowed()
     * setStoreId()
     * @magentoConfigFixture current_store catalog/frontend/parse_url_directives 0
     */
    public function testIsUrlDirectivesParsingAllowed()
    {
        $this->assertFalse($this->helper->isUrlDirectivesParsingAllowed());
        $this->helper->setStoreId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId()
        );
        $this->assertFalse($this->helper->isUrlDirectivesParsingAllowed());
    }

    public function testGetPageTemplateProcessor()
    {
        $this->assertInstanceOf(\Magento\Framework\Filter\Template::class, $this->helper->getPageTemplateProcessor());
    }

    /**
     * @param \Magento\Framework\DataObject $input
     * @param float $expectOutputPrice
     * @param string[] $configs
     * @param string $productClassName
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(
            CustomerFixture::class,
            ['addresses' => [['country_id' => 'US', 'region_id' => 42, 'region' => 'New Jersey']]],
            'customer'
        )
    ]
    #[DataProvider('getTaxPriceDataProvider')]
    public function testGetTaxPrice(
        $input,
        $expectOutputPrice,
        $configs = [],
        $productClassName = 'DefaultProductClass'
    ) {
        $this->setUpDefaultRules();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $product->setTaxClassId($this->taxClasses[$productClassName]);
        $shippingAddress = $this->getCustomerAddress();
        $billingAddress = $shippingAddress;
        foreach ($configs as $config) {
            $this->scopeConfig->setValue($config['path'], $config['value'], ScopeInterface::SCOPE_STORE, 'default');
        }

        $price = $this->helper->getTaxPrice(
            $product,
            $input->getPrice(),
            $input->getIncludingTax(),
            $shippingAddress,
            $billingAddress,
            $this->taxClasses['DefaultCustomerClass'],
            $input->getStore(),
            $input->getPriceIncludesTax(),
            $input->getRoundPrice()
        );
        if ($input->getNotEqual()) {
            $this->assertNotEquals($expectOutputPrice, $price);
        } else {
            $this->assertEqualsWithDelta((float)$expectOutputPrice, $price, 0.0000000001);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getTaxPriceDataProvider()
    {
        $cases = [
            'price is 0' => [
                (new \Magento\Framework\DataObject())->setPrice(0),
                0,
            ],
            'no price conversion, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '3.26',
            ],
            'no price conversion, no round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256),
                '3.256',
            ],
            'price conversion, display including tax, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '3.5',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '0',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_INCLUDING_TAX,
                    ],
                ],
            ],
            'price conversion, display including tax, no round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(false),
                3.5002,
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '0',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_INCLUDING_TAX,
                    ],
                ],
            ],
            'price conversion, display including tax, high rate product tax class, cross boarder trade, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '3.98', // rounding issue: old code expects 3.97
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '0',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_INCLUDING_TAX,
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED,
                        'value' => '1',
                    ],
                ],
                'HigherProductClass',
            ],
            'price include tax, display including tax, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '3.26',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '1',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_INCLUDING_TAX,
                    ],
                ],
            ],
            'price include tax, display excluding tax, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '3.03',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '1',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    ],
                ],
            ],
            'price include tax, display excluding tax, request including tax, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)
                    ->setRoundPrice(true)
                    ->setIncludingTax(true),
                '3.26',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '1',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    ],
                ],
            ],
            'price include tax, display excluding tax, high rate product tax class, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '2.97',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '1',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    ],
                ],
                'HigherProductClass',
            ],
            'price include tax, display excluding tax, high rate product tax class, cross boarder trade, round' => [
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setRoundPrice(true),
                '2.67',
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '1',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED,
                        'value' => '1',
                    ],
                ],
                'HigherProductClass',
            ],
        ];
        return array_merge($cases, self::getExplicitTaxInputCases(), self::getShippingDisplayCases());
    }

    private static function getExplicitTaxInputCases(): array
    {
        $cases = [];
        $prices = [
            'exclusive to default' => [false, null, 3.256],
            'exclusive to exclusive' => [false, false, 3.256],
            'exclusive to inclusive' => [false, true, 3.5002],
            'inclusive to default' => [true, null, 3.256 / 1.075],
            'inclusive to exclusive' => [true, false, 3.256 / 1.075],
            'inclusive to inclusive' => [true, true, 3.256],
        ];
        foreach ([
            \Magento\Tax\Api\TaxCalculationInterface::CALC_UNIT_BASE,
            \Magento\Tax\Api\TaxCalculationInterface::CALC_ROW_BASE,
            \Magento\Tax\Api\TaxCalculationInterface::CALC_TOTAL_BASE,
        ] as $algorithm) {
            foreach ($prices as $basis => [$inputIncludesTax, $outputIncludesTax, $expected]) {
                foreach ([false, true] as $roundPrice) {
                    $name = $algorithm . ' ' . $basis . ' rounding ' . (int)$roundPrice;
                    $cases[$name] = [
                        (new \Magento\Framework\DataObject())
                            ->setPrice(3.256)
                            ->setPriceIncludesTax($inputIncludesTax)
                            ->setIncludingTax($outputIncludesTax)
                            ->setRoundPrice($roundPrice),
                        $roundPrice ? round($expected, 2) : $expected,
                        [
                            ['path' => Config::XML_PATH_ALGORITHM, 'value' => $algorithm],
                            ['path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, 'value' => 0],
                            [
                                'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                                'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                            ],
                            [
                                'path' => Config::XML_PATH_DISPLAY_CART_PRICE,
                                'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                            ],
                            [
                                'path' => Config::XML_PATH_DISPLAY_CART_SHIPPING,
                                'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                            ],
                            ['path' => Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, 'value' => 0],
                            ['path' => Config::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED, 'value' => 0],
                            ['path' => 'shipping/origin/country_id', 'value' => 'US'],
                            ['path' => 'shipping/origin/region_id', 'value' => 12],
                            ['path' => 'shipping/origin/postcode', 'value' => '*'],
                        ],
                    ];
                }
            }
        }
        return $cases;
    }

    private static function getShippingDisplayCases(): array
    {
        $cases = [];
        foreach ([
            Config::DISPLAY_TYPE_EXCLUDING_TAX,
            Config::DISPLAY_TYPE_INCLUDING_TAX,
            Config::DISPLAY_TYPE_BOTH,
        ] as $catalogDisplay) {
            foreach ([0, 1] as $shippingInput) {
                foreach ([
                    Config::DISPLAY_TYPE_EXCLUDING_TAX,
                    Config::DISPLAY_TYPE_INCLUDING_TAX,
                    Config::DISPLAY_TYPE_BOTH,
                ] as $shippingDisplay) {
                    $includingTax = $catalogDisplay !== Config::DISPLAY_TYPE_EXCLUDING_TAX;
                    $cases["catalog display $catalogDisplay shipping input $shippingInput display $shippingDisplay"] = [
                        (new \Magento\Framework\DataObject())
                            ->setPrice(50)
                            ->setIncludingTax($includingTax)
                            ->setRoundPrice(true),
                        $includingTax ? 53.75 : 50.0,
                        [
                            ['path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, 'value' => 0],
                            ['path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, 'value' => $catalogDisplay],
                            [
                                'path' => Config::XML_PATH_DISPLAY_CART_PRICE,
                                'value' => Config::DISPLAY_TYPE_EXCLUDING_TAX,
                            ],
                            ['path' => Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, 'value' => $shippingInput],
                            ['path' => Config::XML_PATH_DISPLAY_CART_SHIPPING, 'value' => $shippingDisplay],
                        ],
                    ];
                }
            }
        }
        return $cases;
    }

    /**
     * Helper function that sets up some default rules
     */
    private function setUpDefaultRules()
    {
        $this->taxClasses = $this->taxRuleFixtureFactory->createTaxClasses([
                ['name' => 'DefaultCustomerClass', 'type' => ClassModel::TAX_CLASS_TYPE_CUSTOMER],
                ['name' => 'DefaultProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
                ['name' => 'HigherProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
            ]);

        $this->taxRates = $this->taxRuleFixtureFactory->createTaxRates([
                ['percentage' => 7.5, 'country' => 'US', 'region' => 42],
                ['percentage' => 7.5, 'country' => 'US', 'region' => 12], // Default store rate
            ]);

        $higherRates = $this->taxRuleFixtureFactory->createTaxRates([
                ['percentage' => 22, 'country' => 'US', 'region' => 42],
                ['percentage' => 10, 'country' => 'US', 'region' => 12], // Default store rate
            ]);

        $this->taxRules = $this->taxRuleFixtureFactory->createTaxRules([
                [
                    'code' => 'Default Rule',
                    'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                    'product_tax_class_ids' => [$this->taxClasses['DefaultProductClass']],
                    'tax_rate_ids' => array_values($this->taxRates),
                    'sort_order' => 0,
                    'priority' => 0,
                ],
                [
                    'code' => 'Higher Rate Rule',
                    'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                    'product_tax_class_ids' => [$this->taxClasses['HigherProductClass']],
                    'tax_rate_ids' => array_values($higherRates),
                    'sort_order' => 0,
                    'priority' => 0,
                ],
            ]);

        // For cleanup
        $this->taxRates = array_merge($this->taxRates, $higherRates);
    }

    /**
     * Get fixture customer address
     *
     * @return \Magento\Customer\Model\Address
     */
    private function getCustomerAddress()
    {
        return DataFixtureStorageManager::getStorage()->get('customer')->getDefaultShippingAddress();
    }

    /**
     * Helper function that tears down some default rules
     */
    private function tearDownDefaultRules()
    {
        if ($this->taxRules) {
            $this->taxRuleFixtureFactory->deleteTaxRules(array_values($this->taxRules));
        }
        if ($this->taxRates) {
            $this->taxRuleFixtureFactory->deleteTaxRates(array_values($this->taxRates));
        }
        if ($this->taxClasses) {
            $this->taxRuleFixtureFactory->deleteTaxClasses(array_values($this->taxClasses));
        }
    }
}
