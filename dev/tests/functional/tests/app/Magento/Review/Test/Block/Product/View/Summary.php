<?php
/**
 * @api
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Block\Product\View;

use Mtf\Block\Block;

/**
 * Reviews frontend block
 *
 */
class Summary extends Block
{
    /**
     * Add review link selector
     *
     * @var string
     */
    protected $addReviewLinkSelector = '.action.add';

    /**
     * View review link selector
     *
     * @var string
     */
    protected $viewReviewLinkSelector = '.action.view';

    /**
     * Get add review link
     *
     * @return \Mtf\Client\Element
     */
    public function getAddReviewLink()
    {
        return $this->_rootElement->find($this->addReviewLinkSelector);
    }

    /**
     * Get view review link
     *
     * @return \Mtf\Client\Element
     */
    public function getViewReviewLink()
    {
        return $this->_rootElement->find($this->viewReviewLinkSelector);
    }
}
