<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

class Grand extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Collect grand total address amount
     *
     * @param   \Magento\Quote\Model\Quote\Address $address
     * @return  $this
     */
    public function collect(\Magento\Quote\Model\Quote\Address $address)
    {
        $totals = array_sum($address->getAllTotalAmounts());
        $baseTotals = array_sum($address->getAllBaseTotalAmounts());

        $address->setGrandTotal($totals);
        $address->setBaseGrandTotal($baseTotals);
        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param   \Magento\Quote\Model\Quote\Address $address
     * @return  $this
     */
    public function fetch(\Magento\Quote\Model\Quote\Address $address)
    {
        $address->addTotal(
            [
                'code' => $this->getCode(),
                'title' => __('Grand Total'),
                'value' => $address->getGrandTotal(),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
