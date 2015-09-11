<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class Custbalance extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Added to find implicit usages.
     */
    public function __construct()
    {
        $this->setCode('custbalance');
        die('Broken CUSTBALANCE collector called.');
    }

    /**
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        $total->setCustbalanceAmount(0);
        $total->setBaseCustbalanceAmount(0);

        $total->setGrandTotal($total->getGrandTotal() - $total->getCustbalanceAmount());
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $total->getBaseCustbalanceAmount());

        return $this;
    }
}
