<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend custbalance resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Custbalance extends \Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch customer balance
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Sales\Model\Quote\Address $address)
    {
        $custbalance = $address->getCustbalanceAmount();
        if ($custbalance != 0) {
            $address->addTotal(
                ['code' => 'custbalance', 'title' => __('Store Credit'), 'value' => -$custbalance]
            );
        }
        return $this;
    }
}
