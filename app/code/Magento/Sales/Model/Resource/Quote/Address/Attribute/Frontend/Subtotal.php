<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend subtotal resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Subtotal extends \Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend
{
    /**
     * Add total
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Sales\Model\Quote\Address $address)
    {
        $address->addTotal(['code' => 'subtotal', 'title' => __('Subtotal'), 'value' => $address->getSubtotal()]);

        return $this;
    }
}
