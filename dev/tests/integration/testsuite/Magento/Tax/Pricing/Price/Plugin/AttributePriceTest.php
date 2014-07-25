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

namespace Magento\Tax\Pricing\Price\Plugin;

use Magento\Tax\Model\ClassModel;
use Magento\Tax\Service\V1\TaxRuleFixtureFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoDataFixture Magento/Customer/_files/customer_address.php
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class AttributePriceTest extends \PHPUnit_Framework_TestCase
{
    /**
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
     * @var \Magento\Tax\Service\V1\TaxRuleFixtureFactory
     */
    private $taxRuleFixtureFactory;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->taxRuleFixtureFactory = new \Magento\Tax\Service\V1\TaxRuleFixtureFactory();
    }

    public function tearDown()
    {
        $this->tearDownDefaultRules();
    }

    /**
     * Tests that the plugin's adjustment config behavior with different tax config.
     *
     * @param bool $isTaxIncludeInBasePrice
     * @param int $priceDisplayType
     * @param bool $isProductTaxClassIdSet
     * @dataProvider getTaxConfigData
     */
    public function testPrepareAdjustmentConfig($isTaxIncludedInBasePrice, $priceDisplayType, $isProductTaxClassIdSet)
    {
        $this->setupDefaultRules();

        $scopeConfig = $this->objectManager->get('Magento\Framework\App\MutableScopeConfig');
        $scopeConfig->setValue(
            Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            $isTaxIncludedInBasePrice,
            ScopeInterface::SCOPE_STORE,
            'default'
        );
        $scopeConfig->setValue(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            $priceDisplayType,
            ScopeInterface::SCOPE_STORE,
            'default'
        );

        $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load(1);
        if ($isProductTaxClassIdSet) {
            $product->setTaxClassId($this->taxClasses['DefaultProductClass']);
        }

        $customerId = 1;
        $attributePrice = $this->objectManager->create(
            'Magento\ConfigurableProduct\Pricing\Price\AttributePrice',
            [
                'saleableItem' => $product,
                'quantity' => 1,
            ]
        );

        $result = $attributePrice->prepareAdjustmentConfig($customerId);
        if ($isProductTaxClassIdSet) {
            $this->assertEquals(7.5, $result['defaultTax']);
            $this->assertEquals(6.5, $result['currentTax']);
        } else {
            $this->assertEquals(0, $result['defaultTax']);
            $this->assertEquals(0, $result['currentTax']);
        }
        $this->assertEquals($isTaxIncludedInBasePrice, $result['includeTax']);
        $this->assertEquals($priceDisplayType === Config::DISPLAY_TYPE_INCLUDING_TAX, $result['showIncludeTax']);
        $this->assertEquals($priceDisplayType === Config::DISPLAY_TYPE_BOTH, $result['showBothPrices']);
        $this->assertEquals($customerId, $result['customerId']);
    }

    public function getTaxConfigData()
    {
        return [
            'default behavior' => [
                false,
                Config::DISPLAY_TYPE_EXCLUDING_TAX,
                null
            ],
            'include tax in base, show include tax in display, no product tax class' => [
                true,
                Config::DISPLAY_TYPE_INCLUDING_TAX,
                null
            ],
            'include tax in base, show both include and exclude tax in display, no product tax class' => [
                true,
                Config::DISPLAY_TYPE_BOTH,
                null
            ],
            'include tax in base, show both include and exclude tax in display, set product tax class' => [
                true,
                Config::DISPLAY_TYPE_BOTH,
                true
            ],
        ];
    }

    private function setUpDefaultRules()
    {
        $this->taxClasses = $this->taxRuleFixtureFactory->createTaxClasses([
            ['name' => 'DefaultCustomerClass', 'type' => ClassModel::TAX_CLASS_TYPE_CUSTOMER],
            ['name' => 'DefaultProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
        ]);

        $this->taxRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 6.5, 'country' => 'US', 'region' => 1],
            ['percentage' => 7.5, 'country' => 'US', 'region' => 12], // Default store rate
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
        ]);
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
