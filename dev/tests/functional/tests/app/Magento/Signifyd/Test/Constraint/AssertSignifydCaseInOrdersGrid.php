<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Constraint;

use Magento\Signifyd\Test\Fixture\SignifydData;
use Magento\Signifyd\Test\Page\Adminhtml\OrdersGrid;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Signifyd Guarantee Status is present in Orders grid.
 */
class AssertSignifydCaseInOrdersGrid extends AbstractConstraint
{
    /**
     * @param string $orderId
     * @param OrdersGrid $ordersGrid
     * @param SignifydData $signifydData
     * @return void
     */
    public function processAssert(
        $orderId,
        OrdersGrid $ordersGrid,
        SignifydData $signifydData
    ) {
        $filter = [
            'id' => $orderId,
            'signifyd_guarantee_status' => $signifydData->getGuaranteeDisposition()
        ];

        $errorMessage = implode(', ', $filter);

        $ordersGrid->open();

        \PHPUnit\Framework\Assert::assertTrue(
            $ordersGrid->getSignifydOrdersGrid()->isRowVisible(array_filter($filter)),
            'Order with following data \'' . $errorMessage . '\' is absent in orders grid.'
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Signifyd guarantee status is displayed in sales orders grid.';
    }
}
