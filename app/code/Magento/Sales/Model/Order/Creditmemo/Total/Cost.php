<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

class Cost extends AbstractTotal
{
    /**
     * Collect total cost of refunded items
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $baseRefundTotalCost = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->getHasChildren()) {
                $baseRefundTotalCost += $item->getBaseCost() * $item->getQty();
            }
        }
        $creditmemo->setBaseCost($baseRefundTotalCost);
        return $this;
    }
}
