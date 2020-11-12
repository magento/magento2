<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Html pager block
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @api
 * @since 100.0.2
 */
class OrderItemsPager extends \Magento\Catalog\Block\Product\Widget\Html\Pager
{
    /**
     * @var int
     */
    protected $_total_item_count;

    /**
     * Return current page
     */
    public function getCurrentPage()
    {
        if (is_object($this->_collection)) {
            return $this->_collection->getCurPage();
        }
        return (int)$this->getRequest()->getParam($this->getPageVarName(), 1);
    }

    /**
     * Set collection for pagination
     *
     * @param Collection $collection
     * @return OrderItemsPager
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection->setCurPage($this->getCurrentPage());
        // If not int - then not limit
        if ((int)$this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        $this->_setFrameInitialized(false);

        return $this;
    }

    /**
     * Set collection for total products count
     *
     * @param int $totalItemCount
     * @return OrderItemsPager
     */
    public function setTotalItemCount($totalItemCount)
    {
        $this->_total_item_count = $totalItemCount;
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
        return $this->_total_item_count;
    }
}
