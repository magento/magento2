<?php
/**
 * Oder statuses grid collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Status;

/**
 * Class \Magento\Sales\Model\ResourceModel\Status\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Status\Collection
{
    /**
     * Join order states table
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinStates();
        return $this;
    }
}
