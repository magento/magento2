<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Source;

/**
 * Google Content Item statues Source
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Statuses
{
    /**
     * Retrieve option array with Google Content item's statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        return ['0' => __('Yes'), '1' => __('No')];
    }
}
