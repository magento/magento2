<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter queue data grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\ResourceModel\Queue\Grid;

/**
 * Class \Magento\Newsletter\Model\ResourceModel\Queue\Grid\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Newsletter\Model\ResourceModel\Queue\Collection
{
    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscribersInfo();
        return $this;
    }
}
