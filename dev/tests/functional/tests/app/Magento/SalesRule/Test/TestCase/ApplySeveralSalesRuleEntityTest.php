<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Fixture\FixtureFactory;


/**
 * Preconditions:
 * 1. Execute before each variation:
 *  - Create sales  rule from dataset using Curl
 *
 * Steps:
 * 1. Create simple product.
 * 2. Apply all created rules.
 * 3. Perform all assertions.
 *
 * @group Sales_Rules_(CS)
 * @ZephyrId MAGETWO-45883
 */
class ApplySeveralSalesRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Inject pages.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Apply several sales rules.
     *
     * @param array $salesRulesOriginal
     * @return array
     */
    public function testApplySeveralSalesRules(array $salesRulesOriginal)
    {
        foreach ($salesRulesOriginal as $key => $salesRule) {
           if ($salesRule == '-') {
               continue;
           }
           $salesRules[$key] = $this->fixtureFactory->createByCode(
               'salesRule',
               ['dataset' => $salesRule]
           );
           $salesRules[$key]->persist();
       }
       $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => 'default']);
       $customer->persist();

       $productForSalesRule1 = $this->fixtureFactory->createByCode(
           'catalogProductSimple',
           ['dataset' => 'simple_for_salesrule_1']
       );
       $productForSalesRule1->persist();

       $productForSalesRule2 = $this->fixtureFactory->createByCode(
           'catalogProductSimple',
           ['dataset' => 'simple_for_salesrule_2']
       );
       $productForSalesRule2->persist();

       return [
           'customer' => $customer,
           'productForSalesRule1' => $productForSalesRule1,
           'productForSalesRule2' => $productForSalesRule2
       ];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
       $this->objectManager->create('\Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep')->run();
    }
}
