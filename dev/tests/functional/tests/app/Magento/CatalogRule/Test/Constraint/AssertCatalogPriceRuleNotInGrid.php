<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleNotInGrid
 */
class AssertCatalogPriceRuleNotInGrid extends AbstractConstraint
{
    /**
     * Assert that Catalog Price Rule is not presented in grid and cannot be found using ID, Rule name
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @return void
     */
    public function processAssert(
        CatalogRule $catalogPriceRule,
        CatalogRuleIndex $pageCatalogRuleIndex
    ) {
        $filter = [
            'rule_id' => $catalogPriceRule->getId(),
            'name' => $catalogPriceRule->getName(),
        ];
        $pageCatalogRuleIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $pageCatalogRuleIndex->getCatalogRuleGrid()->isRowVisible($filter),
            'Catalog Price Rule \'' . $filter['rule_id'] . '\', '
            . 'with name \'' . $filter['name'] . '\', '
            . 'is present in Catalog Price Rule grid.'
        );
    }

    /**
     * Success text that Catalog Price Rule is NOT present in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Catalog Price Rule is NOT present in Catalog Rule grid.';
    }
}
