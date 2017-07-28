<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link;

/**
 * Downloadable Product link purchased resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Purchased extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento class constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('downloadable_link_purchased', 'purchased_id');
    }
}
