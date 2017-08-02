<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

/**
 * @api
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
        return $this->getUrl('customer/*/productReviews', ['_current' => true]);
    }
}
