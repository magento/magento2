<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Reviews section on product edit page.
 */
class Reviews extends Section
{
    /**
     * Product reviews block selector.
     *
     * @var string
     */
    protected $reviews = '[data-index="review"]';

    /**
     * Returns product reviews grid.
     *
     * @return \Magento\Review\Test\Block\Adminhtml\Edit\Product\Grid
     */
    public function getReviewsGrid()
    {
        return $this->blockFactory->create(
            \Magento\Review\Test\Block\Adminhtml\Edit\Product\Grid::class,
            ['element' => $this->_rootElement->find($this->reviews)]
        );
    }
}
