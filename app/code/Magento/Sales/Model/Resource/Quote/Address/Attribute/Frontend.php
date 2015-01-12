<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute;

/**
 * Quote address attribute frontend resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Frontend extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * Fetch totals
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return array
     */
    public function fetchTotals(\Magento\Sales\Model\Quote\Address $address)
    {
        $arr = [];

        return $arr;
    }
}
