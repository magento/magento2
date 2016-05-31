<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

class Grand extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $grandTotal = $creditmemo->getGrandTotal();
        $baseGrandTotal = $creditmemo->getBaseGrandTotal();

        $grandTotal += $creditmemo->getAdjustmentPositive();
        $baseGrandTotal += $creditmemo->getBaseAdjustmentPositive();

        $grandTotal -= $creditmemo->getAdjustmentNegative();
        $baseGrandTotal -= $creditmemo->getBaseAdjustmentNegative();

        $creditmemo->setGrandTotal($grandTotal);
        $creditmemo->setBaseGrandTotal($baseGrandTotal);

        $creditmemo->setAdjustment($creditmemo->getAdjustmentPositive() - $creditmemo->getAdjustmentNegative());
        $creditmemo->setBaseAdjustment(
            $creditmemo->getBaseAdjustmentPositive() - $creditmemo->getBaseAdjustmentNegative()
        );

        return $this;
    }
}
