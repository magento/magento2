<?php
/**
 *  Reviews products admin grid
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Product\Edit\Tab;

/**
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Reviews extends \Magento\Review\Block\Adminhtml\Grid
{
    /**
     * Hide grid mass action elements
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Determine ajax url for grid refresh
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('review/product_reviews/grid', ['_current' => true]);
    }
}
