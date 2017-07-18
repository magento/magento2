<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\TestFramework\Helper\Bootstrap;

class TaxRuleCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Tax/_files/tax_classes.php
     */
    public function testCreateTaxRuleCollectionItem()
    {
        /** @var \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection $collection */
        $collection = Bootstrap::getObjectManager()->get(
            \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection::class
        );
        $dbTaxRulesQty = $collection->count();

        /** @var \Magento\Tax\Model\Calculation\Rule $firstTaxRuleFixture */
        $firstTaxRuleFixture = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class)
            ->registry('_fixture/Magento_Tax_Model_Calculation_Rule');
        $expectedFirstTaxRuleId = $firstTaxRuleFixture->getId();

        if (($dbTaxRulesQty == 0) || ($collection->getFirstItem()->getId() != $expectedFirstTaxRuleId)) {
            $this->fail("Preconditions failed.");
        }
        /** @var \Magento\Tax\Model\TaxRuleCollection $taxRulesCollection */
        $taxRulesCollection = Bootstrap::getObjectManager()
            ->create(\Magento\Tax\Model\TaxRuleCollection::class);
        $collectionTaxRulesQty = $taxRulesCollection->count();
        $this->assertEquals($dbTaxRulesQty, $collectionTaxRulesQty, 'Tax rules quantity is invalid.');
        $taxRule = $taxRulesCollection->getFirstItem()->getData();
        $expectedTaxRuleData = [
            'tax_calculation_rule_id' => $expectedFirstTaxRuleId,
            'code' => 'Test Rule',
            'priority' => '0',
            'position' => '0',
            'calculate_subtotal' => '0',
            'customer_tax_classes' => $firstTaxRuleFixture->getCustomerTaxClassIds(),
            'product_tax_classes' => $firstTaxRuleFixture->getProductTaxClassIds(),
            'tax_rates' => $firstTaxRuleFixture->getTaxRateIds(),
            'tax_rates_codes' => $firstTaxRuleFixture->getTaxRatesCodes()
        ];

        $this->assertEquals($expectedTaxRuleData, $taxRule, 'Tax rule data is invalid.');
    }
}
