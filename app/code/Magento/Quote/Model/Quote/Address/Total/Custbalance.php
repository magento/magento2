<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

class Custbalance extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface|\Magento\Quote\Model\Quote\Address $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $shippingAssignment->setCustbalanceAmount(0);
        $shippingAssignment->setBaseCustbalanceAmount(0);

        $shippingAssignment->setGrandTotal($shippingAssignment->getGrandTotal() - $shippingAssignment->getCustbalanceAmount());
        $shippingAssignment->setBaseGrandTotal($shippingAssignment->getBaseGrandTotal() - $shippingAssignment->getBaseCustbalanceAmount());

        return $this;
    }
}
