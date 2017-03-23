<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 *  - Create sales rule from dataset using Handler
 *
 * Steps:
 * 1. Create simple product.
 * 2. Apply all created rules.
 * 3. Perform all assertions.
 *
 * @group Sales_Rules
 * @ZephyrId MAGETWO-45883
 */
class ApplySeveralSalesRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Fixture factory instance.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject FixtureFactory.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Apply several sales rules.
     *
     * @param array $salesRules
     * @param CatalogProductSimple $productForSalesRule1
     * @param CatalogProductSimple $productForSalesRule2
     */
    public function testApplySeveralSalesRules(
        array $salesRules,
        CatalogProductSimple $productForSalesRule1,
        CatalogProductSimple $productForSalesRule2
    ) {
        // Preconditions
        $productForSalesRule1->persist();
        $productForSalesRule2->persist();

        // Create sales rules
        foreach ($salesRules as $key => $dataSet) {
            $salesRule[$key] = $this->fixtureFactory->createByCode(
                'salesRule',
                ['dataset' => $dataSet]
            );
            $salesRule[$key]->persist();
        }
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep::class)->run();
    }
}
