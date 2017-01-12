<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote\Address\Attribute;

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
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchTotals(\Magento\Quote\Model\Quote\Address $address)
    {
        $arr = [];

        return $arr;
    }
}
