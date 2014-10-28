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

namespace Magento\GoogleShopping\Model\Attribute;

use Magento\Tax\Model\ClassModel;
use Magento\Tax\Service\V1\Data\TaxRuleBuilder;
use Magento\Tax\Service\V1\Data\TaxRateBuilder;
use Magento\Tax\Service\V1\TaxRuleFixtureFactory;

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
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * TaxRule builder
     *
     * @var TaxRuleBuilder
     */
    private $taxRuleBuilder;

    /**
     * TaxRate builder
     *
     * @var TaxRateBuilder
     */
    private $taxRateBuilder;

    /**
     * TaxRuleService
     *
     * @var \Magento\Tax\Service\V1\TaxRuleServiceInterface
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
        $this->taxRateBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
        $this->taxRuleService = $this->objectManager->get('Magento\Tax\Service\V1\TaxRuleServiceInterface');
        $this->taxRuleBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
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
