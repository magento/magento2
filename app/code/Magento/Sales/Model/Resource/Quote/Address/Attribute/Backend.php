<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Quote\Address\Attribute;

/**
 * Quote address attribute backend resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Backend extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Collect totals
     *
     * @param \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collectTotals(\Magento\Sales\Model\Quote\Address $address)
    {
        return $this;
    }
}
