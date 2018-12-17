<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * AdminNotification Inbox model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\Grid;

/**
 * Class Collection
 *
 * @package Magento\AdminNotification\Model\ResourceModel\Grid
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return Collection|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _initSelect() //phpcs:ignore
    {
        parent::_initSelect();
        $this->addRemoveFilter();
        return $this;
    }
}
