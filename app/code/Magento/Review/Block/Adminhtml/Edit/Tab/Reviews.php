<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Block\Adminhtml\Edit\Tab;

use Magento\Review\Block\Adminhtml\Grid;

/**
 * Review tab in adminhtml area.
 *
 * @api
 */
class Reviews extends Grid
{
    /**
     * Hide grid mass action elements.
     *
     * @return Reviews
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
