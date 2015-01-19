<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Constraint;

use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCatalogPriceRuleInGrid
 */
class AssertCatalogPriceRuleInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that data in grid on Catalog Price Rules page according to fixture
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRuleIndex $pageCatalogRuleIndex
     * @param CatalogRule $catalogPriceRuleOriginal
     * @return void
     */
    public function processAssert(
        CatalogRule $catalogPriceRule,
        CatalogRuleIndex $pageCatalogRuleIndex,
        CatalogRule $catalogPriceRuleOriginal = null
    ) {
        $data = ($catalogPriceRuleOriginal === null)
            ? $catalogPriceRule->getData()
            : array_merge($catalogPriceRuleOriginal->getData(), $catalogPriceRule->getData());
        $filter = [
            'name' => $data['name'],
            'is_active' => $data['is_active'],
        ];
        //add ruleWebsite to filter if there is one
        if ($catalogPriceRule->getWebsiteIds() != null) {
            $ruleWebsite = $catalogPriceRule->getWebsiteIds();
            $ruleWebsite = is_array($ruleWebsite) ? reset($ruleWebsite) : $ruleWebsite;
            $filter['rule_website'] = $ruleWebsite;
        }
        //add from_date & to_date to filter if there are ones
        if (isset($data['from_date']) && isset($data['to_date'])) {
            $dateArray['from_date'] = date("M j, Y", strtotime($catalogPriceRule->getFromDate()));
            $dateArray['to_date'] = date("M j, Y", strtotime($catalogPriceRule->getToDate()));
            $filter = array_merge($filter, $dateArray);
        }

        $pageCatalogRuleIndex->open();
        $errorMessage = implode(', ', $filter);
        \PHPUnit_Framework_Assert::assertTrue(
            $pageCatalogRuleIndex->getCatalogRuleGrid()->isRowVisible($filter),
            'Catalog Price Rule with following data: \'' . $errorMessage . '\' '
            . 'is absent in Catalog Price Rule grid.'
        );
    }

    /**
     * Success text that Catalog Price Rule exists in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Catalog Price Rule is present in Catalog Rule grid.';
    }
}
