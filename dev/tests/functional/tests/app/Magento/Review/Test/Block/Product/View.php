<?php
/**
 * @api
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Product;

use Magento\Mtf\Block\Block;

/**
 * Class View
 * Reviews frontend block
 */
class View extends Block
{
    /**
     * Review item selector
     *
     * @var string
     */
    protected $itemSelector = '.review-items .review-item';

    /**
     * Selectors mapping
     *
     * @var array
     */
    protected $selectorsMapping = [
        'nickname' => '.review-author .review-details-value',
        'title' => '.review-title',
        'detail' => '.review-content',
    ];

    /**
     * Is visible review item
     *
     * @return bool
     */
    public function isVisibleReviewItem()
    {
        return $this->_rootElement->find($this->itemSelector)->isVisible();
    }

    /**
     * Get field value for review on product view page
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldValue($field)
    {
        if (isset($this->selectorsMapping[$field])) {
            return $this->_rootElement->find($this->selectorsMapping[$field])->getText();
        }

        return null;
    }
}
