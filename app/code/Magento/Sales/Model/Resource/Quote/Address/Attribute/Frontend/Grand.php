<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend;

/**
 * Quote address attribute frontend grand resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grand extends \Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend
{
    /**
     * Fetch grand total
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function fetchTotals(\Magento\Sales\Model\Quote\Address $address)
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
