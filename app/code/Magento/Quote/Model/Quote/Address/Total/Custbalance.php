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
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $total->setCustbalanceAmount(0);
        $total->setBaseCustbalanceAmount(0);

        $total->setGrandTotal($total->getGrandTotal() - $total->getCustbalanceAmount());
        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $total->getBaseCustbalanceAmount());

        return $this;
    }
}
