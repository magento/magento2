<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Section\Conditions;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;

/**
 * Create a Catalog Price Rules and check whether this attribute visible in Dropdown in Conditions section.
 */
class AssertProductAttributeIsUsedPromoRules extends AbstractConstraint
{
    /**
     * Assert that product attribute can be used on promo rules conditions.
     *
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @param CatalogProductAttribute $attribute
     * @return void
     */
    public function processAssert(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew,
        CatalogProductAttribute $attribute
    ) {
        $catalogRuleIndex->open();
        $catalogRuleIndex->getGridPageActions()->addNew();
        $catalogRuleNew->getEditForm()->openSection('conditions');

        /** @var Conditions $conditionsSection */
        $conditionsSection = $catalogRuleNew->getEditForm()->getSection('conditions');
        \PHPUnit_Framework_Assert::assertTrue(
            $conditionsSection->isAttributeInConditions($attribute),
            'Product attribute can\'t be used on promo rules conditions.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product attribute can be used on promo rules conditions.';
    }
}
