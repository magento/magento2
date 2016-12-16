<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Test Creation for Delete CatalogPriceRuleEntity.
 *
 * Test Flow:
 * Preconditions:
 * 1. Catalog Price Rule is created.
 * 2. Customer is created if needed.
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Marketing > Catalog Price Rules.
 * 3. Select required catalog price rule from preconditions.
 * 4. Click on the "Delete" button.
 * 5. Perform all assertions.
 *
 * @group Catalog_Price_Rules
 * @ZephyrId MAGETWO-25211, MAGETWO-20431
 */
class DeleteCatalogPriceRuleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Page CatalogRuleIndex.
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * Page CatalogRuleNew.
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * Injection data.
     *
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @return void
     */
    public function __inject(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew
    ) {
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
    }

    /**
     * Delete Catalog Price Rule test.
     *
     * @param CatalogRule $catalogPriceRule
     * @param string $product
     * @param Customer|null $customer
     * @return array
     */
    public function test(CatalogRule $catalogPriceRule, $product, Customer $customer = null)
    {
        // Precondition
        $catalogPriceRule->persist();

        if ($customer) {
            $customer->persist();
        }

        $filter = [
            'name' => $catalogPriceRule->getName(),
            'rule_id' => $catalogPriceRule->getId(),
        ];
        // Steps
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->delete();
        $this->catalogRuleNew->getModalBlock()->acceptAlert();
        $products = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $product]
        )->run();

        return [
            'products' => $products['products']
        ];
    }
}
