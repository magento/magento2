<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\ResourceModel\Inbox;

use Magento\AdminNotification\Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * AdminNotification Inbox model
 *
 * @package Magento\AdminNotification\Model\ResourceModel\Inbox
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init(
            Model\Inbox::class,
            Model\ResourceModel\Inbox::class
        );
    }

    /**
     * Add remove filter
     *
     * @return $this
     */
    public function addRemoveFilter()
    {
        $this->getSelect()->where('is_remove=?', 0);
        return $this;
    }
}
