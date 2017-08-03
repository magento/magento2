<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

/**
 * Class \Magento\Sales\Model\Order\Creditmemo\Total\Cost
 *
 * @since 2.0.0
 */
class Cost extends AbstractTotal
{
    /**
     * Collect total cost of refunded items
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     * @since 2.0.0
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
