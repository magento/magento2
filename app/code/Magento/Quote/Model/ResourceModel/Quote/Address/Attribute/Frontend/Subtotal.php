<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend subtotal resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Subtotal extends \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend
{
    /**
     * Add total
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     * @since 2.0.0
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $address->addTotal(['code' => 'subtotal', 'title' => __('Subtotal'), 'value' => $address->getSubtotal()]);

        return $this;
    }
}
