<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cron;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleEdit;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preconditions:
 * 1. Execute before each variation:
 *  - Delete all active catalog price rules
 *  - Create catalog price rule from dataset using Curl
 *
 * Steps:
 * 1. Apply all created rules.
 * 2. Create simple product.
 * 3. Perform all assertions.
 *
 * @group Catalog_Price_Rules
 * @ZephyrId MAGETWO-24780
 */
class ApplyCatalogPriceRulesTest extends AbstractCatalogRuleEntityTest
{
    /* tags */
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const MVP = 'yes';
    /* end tags */

    /**
     * Index number of promo product.
     *
     * @var int
     */
    protected $promo;

    /**
     * Apply catalog price rules.
     *
     * @param array $catalogRules
     * @param CatalogRuleEdit $catalogRuleEdit
     * @param TestStepFactory $stepFactory
     * @param Cron $cron
     * @param bool $isCronEnabled
     * @param Customer $customer
     * @param array $products
     * @param int $promo
     * @return FixtureInterface[]
     */
    public function test(
        array $catalogRules,
        CatalogRuleEdit $catalogRuleEdit,
        TestStepFactory $stepFactory,
        Cron $cron,
        $isCronEnabled = false,
        Customer $customer = null,
        array $products = [],
        $promo = 0
    ) {
        $this->promo = $promo;
        if ($customer !== null) {
            $customer->persist();
        }

        $products = $stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run()['products'];

        foreach ($catalogRules as $catalogRule) {
            foreach ($products as $product) {
                $catalogPriceRule = $this->createCatalogPriceRule($catalogRule, $product, $customer);
                if ($isCronEnabled) {
                    $cron->run();
                    $cron->run();
                } else {
                    $catalogRuleEdit->open(['id' => $catalogPriceRule->getId()]);
                    $this->catalogRuleNew->getFormPageActions()->saveAndApply();
                }
            }
        }
        return ['products' => $products];
    }

    /**
     * Prepare condition for catalog price rule.
     *
     * @param FixtureInterface $product
     * @param array $catalogPriceRule
     * @return array
     */
    protected function prepareCondition(FixtureInterface $product, array $catalogPriceRule)
    {
        $conditionEntity = explode('|', trim($catalogPriceRule['data']['rule'], '[]'))[0];
        $actionName = 'get' . $conditionEntity;
        if (method_exists($this, $actionName)) {
            $result = $this->$actionName($product);
            foreach ($result as $key => $value) {
                $catalogPriceRule['data']['rule'] = str_replace($key, $value, $catalogPriceRule['data']['rule']);
            }
            return $catalogPriceRule;
        } else {
            $message = sprintf('Method "%s" does not exist in %s', $actionName, get_class($this));
            throw new \BadMethodCallException($message);
        }
    }

    /**
     * Add category_id to catalog price rule.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getCategory(FixtureInterface $product)
    {
        $result['%category_id%'] = $product->getDataFieldConfig('category_ids')['source']->getIds()[0];
        return $result;
    }

    /**
     * Add attribute_id to catalog price rule.
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function getAttribute(FixtureInterface $product)
    {
        $attributes = $product->getDataFieldConfig('attribute_set_id')['source']
            ->getAttributeSet()->getDataFieldConfig('assigned_attributes')['source']->getAttributes();
        $result['%attribute_id%'] = $attributes[0]->getAttributeCode();
        $result['%attribute_value%'] = $attributes[0]->getOptions()[$this->promo]['id'];
        return $result;
    }

    /**
     * Create customer with customer group and apply customer group to catalog price rule.
     *
     * @param array $catalogPriceRule
     * @param Customer $customer
     * @return array
     */
    protected function applyCustomerGroup(array $catalogPriceRule, Customer $customer)
    {
        /** @var \Magento\Customer\Test\Fixture\CustomerGroup $customerGroup */
        $customerGroup = $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup();
        $catalogPriceRule['data']['customer_group_ids']['option_0'] = $customerGroup->getCustomerGroupId();

        return $catalogPriceRule;
    }

    /**
     * Create catalog price rule.
     *
     * @param array $catalogPriceRule
     * @param FixtureInterface $product
     * @param Customer $customer
     * @return CatalogRule
     */
    protected function createCatalogPriceRule(
        array $catalogPriceRule,
        FixtureInterface $product,
        Customer $customer = null
    ) {
        if (isset($catalogPriceRule['data']['rule'])) {
            $catalogPriceRule = $this->prepareCondition($product, $catalogPriceRule);
        }
        if ($customer !== null) {
            $catalogPriceRule = $this->applyCustomerGroup($catalogPriceRule, $customer);
        }

        $catalogPriceRule = $this->fixtureFactory->createByCode('catalogRule', $catalogPriceRule);
        $catalogPriceRule->persist();

        return $catalogPriceRule;
    }
}
