<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Website\Grid;

use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;

/**
 * Grid collection
 */
class Collection extends WebsiteCollection
{
    /**
     * Join website and store names
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinGroupAndStore();
        return $this;
    }
}
