<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer
 * 2. Log in as default admin user.
 * 3. Go to Stores > Taxes > Tax Rules.
 * 4. Click 'Add New Tax Rule' button.
 * 5. Assign default rates to rule.
 * 6. Save Tax Rule.
 * 7. Go to Stores > Attributes > Product and add new attribute.
 * 8. Select Fixed Product Tax type and fill attribute label.
 * 9. Save attribute.
 * 10. Go to Stores > Attributes > Attribute Set.
 * 11. Add new attribute set based on default.
 * 12. Add created FPT attribute to Product Details group and fill set name.
 * 13. Save attribute set.
 *
 * Steps:
 * 1. Go to Products > Catalog.
 * 2. Add new product.
 * 3. Select created attribute set.
 * 4. Fill data according to dataset.
 * 5. Save product.
 * 6. Go to Stores > Configuration.
 * 7. Fill FPT and Tax configuration according to data set.
 * 8. Save tax configuration.
 * 9. Go to frontend and login with customer
 * 10. Perform all assertions.
 *
 * @group Tax
 * @ZephyrId MAGETWO-29551
 */
class CreateTaxWithFptTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

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
        $customer = $fixtureFactory->createByCode('customer', ['dataset' => 'johndoe_with_addresses']);
        $customer->persist();
        $attributeSet = $this->fixtureFactory
            ->createByCode('catalogAttributeSet', ['dataset' => 'custom_attribute_set_with_fpt']);
        $attributeSet->persist();
        return [
            'customer' => $customer,
            'attributeSet' => $attributeSet
        ];
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Test product prices with tax.
     *
     * @param string $configData
     * @param Customer $customer
     * @param CatalogAttributeSet $attributeSet
     * @param array $productData
     * @return array
     */
    public function test(
        $productData,
        $configData,
        Customer $customer,
        CatalogAttributeSet $attributeSet
    ) {
        $this->fixtureFactory->createByCode('taxRule', ['dataset' => 'tax_rule_default'])->persist();
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataset' => $productData, 'data' => ['attribute_set_id' => ['attribute_set' => $attributeSet]]]
        );
        $product->persist();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $this->loginCustomer($customer);

        return ['product' => $product];
    }

    /**
     * Tear down after tests.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class)->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'default_tax_configuration,shipping_tax_class_taxable_goods_rollback']
        )->run();
    }
}
