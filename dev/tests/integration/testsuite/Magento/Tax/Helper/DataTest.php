<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Helper;

use Magento\Tax\Model\ClassModel;
use Magento\Tax\Service\V1\TaxRuleFixtureFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tax helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    private $helper;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
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

    /** @var \Magento\Framework\App\MutableScopeConfig */
    private $scopeConfig;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $helper \Magento\Tax\Helper\Data */
        $this->helper = $this->objectManager->get('Magento\Tax\Helper\Data');
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();
        $this->scopeConfig = $this->objectManager->get('Magento\Framework\App\MutableScopeConfig');
    }

    protected function tearDown()
    {
        $this->tearDownDefaultRules();
    }

    /**
     * @magentoConfigFixture default_store tax/classes/default_customer_tax_class 1
     */
    public function testGetDefaultCustomerTaxClass()
    {
        $this->assertEquals(1, $this->helper->getDefaultCustomerTaxClass());
    }

    /**
     * @magentoConfigFixture default_store tax/classes/default_product_tax_class 1
     */
    public function testGetDefaultProductTaxClass()
    {
        $this->assertEquals(1, $this->helper->getDefaultProductTaxClass());
    }

    /**
     * @param \Magento\Framework\Object $input
     * @param float $expectOutputPrice
     * @param string[] $configs
     * @param string $productClassName
     *
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDbIsolation enabled
     * @dataProvider getPriceDataProvider
     */
    public function testGetPrice($input, $expectOutputPrice, $configs = [], $productClassName = 'DefaultProductClass')
    {
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

        $price = $this->helper->getPrice(
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
        $this->assertEquals($expectOutputPrice, $price);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPriceDataProvider()
    {
        return [
            'price is 0' => [
                (new \Magento\Framework\Object())->setPrice(0),
                0
            ],
            'no price conversion, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
                '3.26'
            ],
            'no price conversion, no round' => [
                (new \Magento\Framework\Object())->setPrice(3.256),
                '3.256'
            ],
            'price conversion, display including tax, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
                ]
            ],
            'price conversion, display including tax, no round' => [
                (new \Magento\Framework\Object())->setPrice(3.256),
                '3.5',  // rounding issue: old code expects 3.5002
                [
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
                        'value' => '0',
                    ],
                    [
                        'path' => Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
                        'value' => Config::DISPLAY_TYPE_INCLUDING_TAX,
                    ],
                ]
            ],
            'price conversion, display including tax, high rate product tax class, cross boarder trade, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
                ]
            ],
            'price include tax, display excluding tax, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
                ]
            ],
            'price include tax, display excluding tax, request including tax, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)
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
                ]
            ],
            'price include tax, display excluding tax, high rate product tax class, round' => [
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
                (new \Magento\Framework\Object())->setPrice(3.256)->setRoundPrice(true),
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
}
