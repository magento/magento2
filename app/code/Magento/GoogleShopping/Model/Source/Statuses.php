<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
