<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\ResourceModel\Link;

/**
 * Downloadable Product link purchased resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Purchased extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('downloadable_link_purchased', 'purchased_id');
    }
}
