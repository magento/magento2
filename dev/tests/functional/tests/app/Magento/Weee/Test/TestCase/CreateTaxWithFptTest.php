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

namespace Magento\Weee\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Core\Test\Fixture\ConfigData;
use Mtf\ObjectManager;

/**
 * Test CreateTaxWithFptTest
 *
 * Test Flow:
 *
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
 * 10. Go to Stores > Attributes > Product Template.
 * 11. Add new product template based on default.
 * 12. Add created FPT attribute to Product Details group and fill set name.
 * 13. Save attribute set.
 *
 * Steps:
 * 1. Go to Products > Catalog.
 * 2. Add new product.
 * 3. Select created product template.
 * 4. Fill data according to dataset.
 * 5. Save product.
 * 6. Go to Stores > Configuration.
 * 7. Fill FPT and Tax configuration according to data set.
 * 8. Save tax configuration.
 * 9. Go to frontend and login with customer
 * 10. Perform all assertions.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-29551
 */
class CreateTaxWithFptTest extends Injectable
{
    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $customer = $fixtureFactory->createByCode('customerInjectable', ['dataSet' => 'johndoe_with_addresses']);
        $customer->persist();
        $taxRule = $fixtureFactory->createByCode('taxRule', ['dataSet' => 'tax_rule_default']);
        $taxRule->persist();
        $productTemplate = $this->fixtureFactory
            ->createByCode('catalogAttributeSet', ['dataSet' => 'custom_attribute_set_with_fpt']);
        $productTemplate->persist();
        return [
            'customer' => $customer,
            'productTemplate' => $productTemplate
        ];
    }

    /**
     * Login customer
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomer(CustomerInjectable $customer)
    {
        $this->objectManager->create(
            '\Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
    }

    /**
     * Test product prices with tax
     *
     * @param ConfigData $config
     * @param CustomerInjectable $customer
     * @param CatalogAttributeSet $productTemplate
     * @param array $productData
     * @return array
     */
    public function test(
        $productData,
        ConfigData $config,
        CustomerInjectable $customer,
        CatalogAttributeSet $productTemplate
    ) {
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => $productData, 'data' => ['attribute_set_id' => ['attribute_set' => $productTemplate]]]
        );
        $product->persist();
        $config->persist();
        $this->loginCustomer($customer);
        return ['product' => $product];
    }

    /**
     * Tear down after tests
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        ObjectManager::getInstance()->create('\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep')->run();
        ObjectManager::getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'default_tax_configuration']
        )->run();
    }
}
