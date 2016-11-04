<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Taxes > Tax Rules.
 * 3. Click 'Add New Tax Rule' button.
 * 4. Assign 3 different rates for different addresses
 * 5. Save Tax Rate.
 * 6. Go to Products > Catalog.
 * 7. Add new product.
 * 8. Fill data according to dataset.
 * 9. Save product.
 * 10. Go to Stores > Configuration.
 * 11. Fill Tax configuration according to data set.
 * 12. Save tax configuration.
 * 13. Register two customers on front end that will match two different rates
 * 14. Login with each customer and verify prices
 *
 * @group Tax
 * @ZephyrId MAGETWO-29052
 */
class TaxWithCrossBorderTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const STABLE = 'no';
    /* end tags */

    /**
     * Fixture SalesRule.
     *
     * @var SalesRule
     */
    protected $salesRule;

    /**
     * Fixture CatalogRule.
     *
     * @var CatalogRule
     */
    protected $catalogRule;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;

        return ['customers' => $this->createCustomers()];
    }

    /**
     * Injection data.
     *
     * @return void
     */
    public function __inject()
    {
        // TODO: Move test set up to "__prepare" method after fix bug MAGETWO-29331
        $taxRule = $this->fixtureFactory->createByCode('taxRule', ['dataset' => 'cross_border_tax_rule']);
        $taxRule->persist();
    }

    /**
     * Create customers.
     *
     * @return array $customers
     */
    protected function createCustomers()
    {
        $customersData = ['johndoe_unique_TX', 'johndoe_unique'];
        $customers = [];
        foreach ($customersData as $customerData) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => $customerData]);
            $customer->persist();
            $customers[] = $customer;
        }
        return $customers;
    }

    /**
     * Test product prices with tax.
     *
     * @param CatalogProductSimple $product
     * @param string $configData
     * @param SalesRule $salesRule [optional]
     * @param CatalogRule $catalogRule [optional]
     * @return void
     */
    public function test(
        CatalogProductSimple $product,
        $configData,
        SalesRule $salesRule = null,
        CatalogRule $catalogRule = null
    ) {
        //Preconditions
        if ($salesRule !== null) {
            $salesRule->persist();
            $this->salesRule = $salesRule;
        }
        if ($catalogRule !== null) {
            $catalogRule->persist();
            $this->catalogRule = $catalogRule;
        }
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $product->persist();
    }

    /**
     * Tear down after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (isset($this->salesRule)) {
            $this->objectManager->create(\Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep::class)->run();
            $this->salesRule = null;
        }
        if (isset($this->catalogRule)) {
            $this->objectManager->create(\Magento\CatalogRule\Test\TestStep\DeleteAllCatalogRulesStep::class)->run();
            $this->catalogRule = null;
        }

        // TODO: Move set default configuration to "tearDownAfterClass" method after fix bug MAGETWO-29331
        $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class)->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'default_tax_configuration']
        )->run();
    }
}
