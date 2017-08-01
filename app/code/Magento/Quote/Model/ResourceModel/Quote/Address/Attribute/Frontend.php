<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute;

/**
 * Quote address attribute frontend resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Frontend extends \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
{
    /**
     * Fetch totals
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $arr = [];

        return $arr;
    }
}
