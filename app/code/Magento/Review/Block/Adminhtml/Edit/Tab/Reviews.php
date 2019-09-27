<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Edit\Tab;

/**
 * @api
 * @since 100.0.2
 */
class Reviews extends \Magento\Review\Block\Adminhtml\Grid
{
    /**
     * Hide grid mass action elements
     *
     * @return \Magento\Customer\Block\Adminhtml\Edit\Tab\Reviews
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
        return $this->getUrl('review/customer/productReviews', ['_current' => true]);
    }
}
