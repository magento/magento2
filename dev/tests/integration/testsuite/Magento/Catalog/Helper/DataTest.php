<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxRuleFixtureFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tax helper
     *
     * @var \Magento\Catalog\Helper\Data
     */
    private $helper;

    /**
     * Object Manager
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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get('Magento\Catalog\Helper\Data');
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();
        $this->scopeConfig = $this->objectManager->get('Magento\Framework\App\MutableScopeConfig');
    }

    protected function tearDown()
    {
        $this->tearDownDefaultRules();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetBreadcrumbPath()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $category->load(5);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_category', $category);

        try {
            $path = $this->helper->getBreadcrumbPath();
            $this->assertInternalType('array', $path);
            $this->assertEquals(['category3', 'category4', 'category5'], array_keys($path));
            $this->assertArrayHasKey('label', $path['category3']);
            $this->assertArrayHasKey('link', $path['category3']);
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
            throw $e;
        }
    }

    public function testGetCategory()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_category', $category);
        try {
            $this->assertSame($category, $this->helper->getCategory());
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('current_category');
            throw $e;
        }
    }

    public function testGetProduct()
    {
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_product', $product);
        try {
            $this->assertSame($product, $this->helper->getProduct());
            $objectManager->get('Magento\Framework\Registry')->unregister('current_product');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('current_product');
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
        $objectManager->get('Magento\Framework\Registry')->register('attribute_type_hidden_fields', 'test');
        try {
            $this->assertEquals('test', $this->helper->getAttributeHiddenFields());
            $objectManager->get('Magento\Framework\Registry')->unregister('attribute_type_hidden_fields');
        } catch (\Exception $e) {
            $objectManager->get('Magento\Framework\Registry')->unregister('attribute_type_hidden_fields');
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
                'Magento\Store\Model\StoreManagerInterface'
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
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId()
        );
        $this->assertFalse($this->helper->isUrlDirectivesParsingAllowed());
    }

    public function testGetPageTemplateProcessor()
    {
        $this->assertInstanceOf('Magento\Framework\Filter\Template', $this->helper->getPageTemplateProcessor());
    }

    /**
     * @param \Magento\Framework\DataObject $input
     * @param float $expectOutputPrice
     * @param string[] $configs
     * @param string $productClassName
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDbIsolation enabled
     * @dataProvider getTaxPriceDataProvider
     */
    public function testGetTaxPrice(
        $input,
        $expectOutputPrice,
        $configs = [],
        $productClassName = 'DefaultProductClass'
    ) {
        $this->setUpDefaultRules();
        $fixtureProductId = 1;
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create('Magento\Catalog\Model\Product')->load($fixtureProductId);
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
            $this->assertEquals($expectOutputPrice, $price);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTaxPriceDataProvider()
    {
        return [
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
                (new \Magento\Framework\DataObject())->setPrice(3.256)->setNotEqual(true),
                '3.5',  // should be not equal to rounded value (eg, 3.5045009999999999)
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
        $fixtureCustomerId = 1;
        $customerAddress = $this->objectManager->create('Magento\Customer\Model\Address')->load($fixtureCustomerId);
        /** Set data which corresponds tax class fixture */
        $customerAddress->setCountryId('US')->setRegionId(42)->save();
        return $customerAddress;
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
