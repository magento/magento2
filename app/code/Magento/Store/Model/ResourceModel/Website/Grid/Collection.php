<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Website\Grid;

/**
 * Grid collection
 * @since 2.0.0
 */
class Collection extends \Magento\Store\Model\ResourceModel\Website\Collection
{
    /**
     * Join website and store names
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinGroupAndStore();
        return $this;
    }
}
