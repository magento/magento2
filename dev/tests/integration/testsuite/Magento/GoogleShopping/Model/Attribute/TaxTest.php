<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Model\Attribute;

use Magento\Tax\Api\Data\TaxRateDataBuilde as TaxRateBuilder;
use Magento\Tax\Api\Data\TaxRuleDataBuilder;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxRuleFixtureFactory;

/**
 * Tests GoogleShopping\Model\Attribute\Tax
 */
class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleShopping\Model\Attribute\Tax
     */
    protected $googleShoppingTaxAttribute;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * TaxRule builder
     *
     * @var TaxRuleDataBuilder
     */
    private $taxRuleBuilder;

    /**
     * TaxRate builder
     *
     * @var TaxRateDataBuilder
     */
    private $taxRateBuilder;

    /**
     * TaxRuleService
     *
     * @var \Magento\Tax\Api\TaxRuleRepositoryInterface
     */
    private $taxRuleService;

    /**
     * Helps in creating required tax rules.
     *
     * @var TaxRuleFixtureFactory
     */
    private $taxRuleFixtureFactory;

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->googleShoppingTaxAttribute = $this->objectManager
            ->create('Magento\GoogleShopping\Model\Attribute\Tax');
        $this->taxRateBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxRateDataBuilder');
        $this->taxRuleService = $this->objectManager->get('Magento\Tax\Api\TaxRuleRepositoryInterface');
        $this->taxRuleBuilder = $this->objectManager->create('Magento\Tax\Api\Data\TaxRuleDataBuilder');
        $this->taxRuleFixtureFactory = new TaxRuleFixtureFactory();
        $this->setUpDefaultRules();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testConvertAttributeWithSimpleProduct()
    {
        $defaultProductTaxClassProduct = $this->objectManager->create('Magento\Catalog\Model\Product');
        $defaultProductTaxClassProduct->load(1);
        $defaultProductTaxClassProduct->setTaxClassId($this->taxClasses['DefaultProductClass']);

        $defaultGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $defaultEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($defaultProductTaxClassProduct, $defaultGoogleShoppingEntry);

        $this->assertEquals(2, count($defaultEntry->getTaxes()));

        foreach ($defaultEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            $this->assertEquals(7.5, round($tax->__get('tax_rate'), 1));
            $this->assertTrue($tax->__get('tax_region') == 'NM' || $tax->__get('tax_region') == 'CA');
        }

        $higherProductTaxClassProduct = $this->objectManager->create('Magento\Catalog\Model\Product');
        $higherProductTaxClassProduct->load(1);
        $higherProductTaxClassProduct->setTaxClassId($this->taxClasses['HigherProductClass']);

        $higherGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $higherEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($higherProductTaxClassProduct, $higherGoogleShoppingEntry);

        $this->assertEquals(2, count($higherEntry->getTaxes()));

        foreach ($higherEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            if ($tax->__get('tax_region') == 'NM') {
                $this->assertEquals(22.0, round($tax->__get('tax_rate'), 1));
            } elseif ($tax->__get('tax_region') == 'CA') {
                $this->assertEquals(10.0, round($tax->__get('tax_rate'), 1));
            } else {
                $this->fail('Invalid tax region');
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_group_prices.php
     */
    public function testConvertAttributeWithProductGroup()
    {
        $defaultProductTaxClassProduct = $this->objectManager->create('Magento\Catalog\Model\Product');
        $defaultProductTaxClassProduct->load(1);
        $defaultProductTaxClassProduct->setTaxClassId($this->taxClasses['DefaultProductClass']);

        $defaultGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $defaultEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($defaultProductTaxClassProduct, $defaultGoogleShoppingEntry);

        $this->assertEquals(2, count($defaultEntry->getTaxes()));

        foreach ($defaultEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            $this->assertEquals(7.5, round($tax->__get('tax_rate'), 1));
            $this->assertTrue($tax->__get('tax_region') == 'NM' || $tax->__get('tax_region') == 'CA');
        }

        $higherProductTaxClassProduct = $this->objectManager->create('Magento\Catalog\Model\Product');
        $higherProductTaxClassProduct->load(1);
        $higherProductTaxClassProduct->setTaxClassId($this->taxClasses['HigherProductClass']);

        $higherGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $higherEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($higherProductTaxClassProduct, $higherGoogleShoppingEntry);

        $this->assertEquals(2, count($higherEntry->getTaxes()));

        foreach ($higherEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            if ($tax->__get('tax_region') == 'NM') {
                $this->assertEquals(22.0, round($tax->__get('tax_rate'), 1));
            } elseif ($tax->__get('tax_region') == 'CA') {
                $this->assertEquals(10.0, round($tax->__get('tax_rate'), 1));
            } else {
                $this->fail('Invalid tax region');
            }
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testConvertAttributeWithMultipleProducts()
    {
        $productA = $this->objectManager->create('Magento\Catalog\Model\Product');
        $productA->load(10);
        $productA->setTaxClassId($this->taxClasses['DefaultProductClass']);
        $productAGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $productAEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($productA, $productAGoogleShoppingEntry);

        $this->assertEquals(2, count($productAEntry->getTaxes()));
        foreach ($productAEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            $this->assertEquals(7.5, round($tax->__get('tax_rate'), 1));
            $this->assertTrue($tax->__get('tax_region') == 'NM' || $tax->__get('tax_region') == 'CA');
        }

        $productB = $this->objectManager->create('Magento\Catalog\Model\Product');
        $productB->load(11);
        $productB->setTaxClassId($this->taxClasses['HigherProductClass']);
        $productBGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $productBEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($productB, $productBGoogleShoppingEntry);

        $this->assertEquals(2, count($productBEntry->getTaxes()));
        foreach ($productBEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            if ($tax->__get('tax_region') == 'NM') {
                $this->assertEquals(22.0, round($tax->__get('tax_rate'), 1));
            } elseif ($tax->__get('tax_region') == 'CA') {
                $this->assertEquals(10.0, round($tax->__get('tax_rate'), 1));
            } else {
                $this->fail('Invalid tax region');
            }
        }

        $productC = $this->objectManager->create('Magento\Catalog\Model\Product');
        $productC->load(12);
        $productC->setTaxClassId($this->taxClasses['HighestProductClass']);
        $productCGoogleShoppingEntry = $this->objectManager
            ->create('Magento\Framework\Gdata\Gshopping\Entry');
        $productCEntry = $this->googleShoppingTaxAttribute
            ->convertAttribute($productC, $productCGoogleShoppingEntry);

        $this->assertEquals(2, count($productCEntry->getTaxes()));
        foreach ($productCEntry->getTaxes() as $tax) {
            $this->assertEquals('US', $tax->__get('tax_country'));
            if ($tax->__get('tax_region') == 'NM') {
                $this->assertEquals(22.5, round($tax->__get('tax_rate'), 1));
            } elseif ($tax->__get('tax_region') == 'CA') {
                $this->assertEquals(15.0, round($tax->__get('tax_rate'), 1));
            } else {
                $this->fail('Invalid tax_region');
            }
        }
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
            ['name' => 'HighestProductClass', 'type' => ClassModel::TAX_CLASS_TYPE_PRODUCT],
        ]);

        $this->taxRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 7.5, 'country' => 'US', 'region' => 42],
            ['percentage' => 7.5, 'country' => 'US', 'region' => 12], // Default store rate
            ['percentage' => 10, 'country' => 'MX', 'region' => 99],
        ]);

        $higherRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 22, 'country' => 'US', 'region' => 42],
            ['percentage' => 10, 'country' => 'US', 'region' => 12], // Default store rate
            ['percentage' => 15, 'country' => 'MX', 'region' => 99],
        ]);

        $highestRates = $this->taxRuleFixtureFactory->createTaxRates([
            ['percentage' => 22.5, 'country' => 'US', 'region' => 42],
            ['percentage' => 15, 'country' => 'US', 'region' => 12], // Default store rate
            ['percentage' => 20, 'country' => 'MX', 'region' => 99],
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
            [
                'code' => 'Highest Rate Rule',
                'customer_tax_class_ids' => [$this->taxClasses['DefaultCustomerClass'], 3],
                'product_tax_class_ids' => [$this->taxClasses['HighestProductClass']],
                'tax_rate_ids' => array_values($highestRates),
                'sort_order' => 1,
                'priority' => 1,
            ],
        ]);

        // For cleanup
        $this->taxRates = array_merge($this->taxRates, $higherRates, $highestRates);
    }

    /**
     * Helper function that tears down some default rules
     */
    public function tearDown()
    {
        $this->taxRuleFixtureFactory->deleteTaxRules(array_values($this->taxRules));
        $this->taxRuleFixtureFactory->deleteTaxRates(array_values($this->taxRates));
        $this->taxRuleFixtureFactory->deleteTaxClasses(array_values($this->taxClasses));
    }
}
