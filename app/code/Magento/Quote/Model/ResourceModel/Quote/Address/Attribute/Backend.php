<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute;

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
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collectTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        return $this;
    }
}
