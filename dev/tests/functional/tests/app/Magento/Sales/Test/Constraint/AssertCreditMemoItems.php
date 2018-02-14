<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\CreditMemoIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesCreditMemoView;

/**
 * Class AssertCreditMemoItems
 * Assert credit memo items on credit memo view page
 */
class AssertCreditMemoItems extends AbstractAssertItems
{
    /**
     * Assert credit memo items on credit memo view page
     *
     * @param CreditMemoIndex $creditMemoIndex
     * @param SalesCreditMemoView $salesCreditMemoView
     * @param OrderInjectable $order
     * @param array $ids
     * @param array|null $data [optional]
     * @return void
     */
    public function processAssert(
        CreditMemoIndex $creditMemoIndex,
        SalesCreditMemoView $salesCreditMemoView,
        OrderInjectable $order,
        array $ids,
        array $data = null
    ) {
        $creditMemoIndex->open();
        $orderId = $order->getId();
        $productsData = $this->prepareOrderProducts($order, $data['items_data']);
        foreach ($ids['creditMemoIds'] as $creditMemoId) {
            $filter = [
                'order_id' => $orderId,
                'id' => $creditMemoId,
            ];
            $creditMemoIndex->getCreditMemoGrid()->searchAndOpen($filter);
            $itemsData = $this->preparePageItems($salesCreditMemoView->getItemsBlock()->getData());
            $error = $this->verifyData($productsData, $itemsData);
            \PHPUnit_Framework_Assert::assertEmpty($error, $error);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All credit memo products are present in credit memo view page.';
    }
}
