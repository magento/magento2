<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Quote\Model\Quote\Address\Total;

class Custbalance extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Quote\Model\Quote\Address $address)
    {
        $address->setCustbalanceAmount(0);
        $address->setBaseCustbalanceAmount(0);

        $address->setGrandTotal($address->getGrandTotal() - $address->getCustbalanceAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $address->getBaseCustbalanceAmount());

        return $this;
    }
}
