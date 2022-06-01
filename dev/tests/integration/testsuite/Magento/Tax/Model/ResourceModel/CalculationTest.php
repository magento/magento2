<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class CalculationTest extends TestCase
{
    /** @var $objectManager ObjectManager */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test that Tax Rate applied only once
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testGetRate()
    {
        $taxRule = $this->objectManager->get(Registry::class)
            ->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $customerTaxClasses = $taxRule->getCustomerTaxClassIds();
        $productTaxClasses = $taxRule->getProductTaxClassIds();
        $taxRate = $this->objectManager->get(Registry::class)
            ->registry('_fixture/Magento_Tax_Model_Calculation_Rate');
        $data = new DataObject();
        $data->setData(
            [
                'tax_country_id' => 'US',
                'taxregion_id' => '12',
                'tax_postcode' => '5555',
                'customer_class_id' => $customerTaxClasses[0],
                'product_class_id' => $productTaxClasses[0],
            ]
        );
        $taxCalculation = $this->objectManager->get(Calculation::class);
        $this->assertEquals($taxRate->getRateIds(), $taxCalculation->getRate($data));
    }

    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento\Tax\Test\Fixture\TaxClass with:{"class_name":"CustomerTaxClass1", "class_type":"CUSTOMER"} as:c1
     * @magentoDataFixture Magento\Tax\Test\Fixture\TaxClass with:{"class_name":"ProductTaxClass1", "class_type":"PRODUCT"} as:p1
     * @magentoDataFixture Magento\Tax\Test\Fixture\TaxRate with:{"code":"*", "rate":"10", "tax_country_id":"PT", "tax_postcode":"96*", "tax_region_id":"12"} as:rate
     * @magentoDataFixture Magento\Tax\Test\Fixture\TaxRule with:{"code":"Test Rule Portugal", "customer_tax_class_ids":["$c1.id$"], "product_tax_class_ids":["$p1.id$"], "tax_rate_ids":["$rate.id$"]} as:rule
     */
    public function testGetRateForPortugal()
    {
        /** @var TaxRuleRepositoryInterface $taxRule */
        $taxRule = $this->objectManager->get(TaxRuleRepositoryInterface::class);
        $customerTaxClasses = $taxRule->get($this->fixtures->get('rule')->getId())->getCustomerTaxClassIds();
        $productTaxClasses = $taxRule->get($this->fixtures->get('rule')->getId())->getProductTaxClassIds();

        /** @var $taxRate TaxRateRepositoryInterface $taxRule */
        $taxRate = $this->objectManager->get(TaxRateRepositoryInterface::class);
        $data = new DataObject([
            'country_id' => 'PT',
            'region_id' => '12',
            'postcode' => '9600-111',
            'customer_class_id' => $customerTaxClasses[0],
            'product_class_id' => $productTaxClasses[0],
        ]);
        $taxCalculation = $this->objectManager->get(Calculation::class);

        $this->assertEquals(
            $taxRate->get($this->fixtures->get('rate')->getId())->getRate(),
            $taxCalculation->getRate($data)
        );
    }
}
