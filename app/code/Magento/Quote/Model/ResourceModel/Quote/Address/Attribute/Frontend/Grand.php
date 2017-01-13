<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend grand resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grand extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch grand total
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $address->addTotal(
            [
                'code' => 'grand_total',
                'title' => __('Grand Total'),
                'value' => $address->getGrandTotal(),
                'area' => 'footer',
            ]
        );
        return $this;
    }
}
