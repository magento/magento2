<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Theme\Block\Html\Pager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Html pager block
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class OrderItemsPager extends Pager
{
    /**
     * @var int
     */
    private $totalItemCount;

    /**
     * Set collection for total products count
     *
     * @param int $totalItemCount
     * @return OrderItemsPager
     */
    public function setTotalItemCount($totalItemCount)
    {
        $this->totalItemCount = $totalItemCount;
        return $this;
    }

    /**
     * Get last number
     *
     * @return int
     * @throws LocalizedException
     */
    public function getLastNum()
    {
        $collection = $this->getCollection();

        $childCollection = clone $collection;
        $childCollection->clear()->addFieldToFilter('parent_item_id', ['null' => true]);

        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $childCollection->count();
    }

    /**
     * Retrieve total number of products
     *
     * @return int
     */
    public function getTotalNum()
    {
        return $this->totalItemCount;
    }
}
