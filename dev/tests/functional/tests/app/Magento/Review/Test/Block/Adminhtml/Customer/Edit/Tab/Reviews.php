<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Reviews tab on customer edit page.
 */
class Reviews extends Tab
{
    /**
     * Product reviews block selector.
     *
     * @var string
     */
    protected $reviews = '#reviwGrid';

    /**
     * Returns product reviews grid.
     *
     * @return \Magento\Review\Test\Block\Adminhtml\Grid
     */
    public function getReviewsGrid()
    {
        return $this->blockFactory->create(
            'Magento\Review\Test\Block\Adminhtml\Grid',
            ['element' => $this->_rootElement->find($this->reviews)]
        );
    }
}
