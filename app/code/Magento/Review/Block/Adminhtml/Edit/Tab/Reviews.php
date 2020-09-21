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
 * @since 100.4.0
 */
class Reviews extends Grid
{
    /**
     * Hide grid mass action elements.
     *
     * @return Reviews
     * @since 100.4.0
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Determine ajax url for grid refresh
     *
     * @return string
     * @since 100.4.0
     */
    public function getGridUrl()
    {
        return $this->getUrl('review/customer/productReviews', ['_current' => true]);
    }
}
