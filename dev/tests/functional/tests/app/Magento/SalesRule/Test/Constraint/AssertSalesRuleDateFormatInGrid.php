<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteIndex;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Sales Rule dates displayed in correct format after interface locale switch.
 */
class AssertSalesRuleDateFormatInGrid extends AbstractConstraint
{
    /**
     * Assert sales rule from_date displayed in correct format after locale switch.
     * Assert sales rule to_date displayed in correct format after locale switch.
     *
     * @param SalesRule $salesRule
     * @param PromoQuoteIndex $promoQuoteIndex
     * @param PromoQuoteEdit $promoQuoteEdit
     * @param string $dateFormat
     * @return void
     */
    public function processAssert(
        SalesRule $salesRule,
        PromoQuoteIndex $promoQuoteIndex,
        PromoQuoteEdit $promoQuoteEdit,
        $dateFormat
    ) {
        $filter = [
            'name' => $salesRule->getName(),
        ];

        $expectedFromDate = date($dateFormat, strtotime($salesRule->getFromDate()));
        $expectedToDate = date($dateFormat, strtotime($salesRule->getToDate()));

        $promoQuoteIndex->open();
        $promoQuoteIndex->getPromoQuoteGrid()->searchAndOpen($filter);
        $tabData = $promoQuoteEdit->getSalesRuleForm()->getTab('rule_information')->getDataFormTab();

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedFromDate,
            $tabData['from_date'],
            'Sales Rule from_date displayed in wrong format after locale switch.'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedToDate,
            $tabData['to_date'],
            'Sales Rule to_date displayed in wrong format after locale switch.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales Rule dates displayed in expected format.';
    }
}
