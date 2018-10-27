<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that specified in data set buttons exist on order page in backend.
 */
class AssertOrderButtonsAvailable extends AbstractConstraint
{
    /**
     * Assert that specified in data set buttons exist on order page in backend.
     *
     * @param SalesOrderView $salesOrderView
     * @param string $orderButtonsAvailable
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, $orderButtonsAvailable)
    {
        $buttons = explode(',', $orderButtonsAvailable);
        $absentButtons = [];
        $actionsBlock = $salesOrderView->getPageActions();

        foreach ($buttons as $button) {
            $button = trim($button);
            if (!$actionsBlock->isActionButtonVisible($button)) {
                $absentButtons[] = $button;
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty(
            $absentButtons,
            "Next buttons was not found on page: \n" . implode(";\n", $absentButtons)
        );
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "All buttons are available on order page.";
    }
}
