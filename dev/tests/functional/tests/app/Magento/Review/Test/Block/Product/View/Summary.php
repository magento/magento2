<?php
/**
 * @api
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Product\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;

/**
 * Reviews frontend block.
 */
class Summary extends Block
{
    /**
     * Add review link selector.
     *
     * @var string
     */
    protected $addReviewLinkSelector = '.action.add';

    /**
     * View review link selector.
     *
     * @var string
     */
    protected $viewReviewLinkSelector = '.action.view';

    /**
     * Get add review link.
     *
     * @return ElementInterface
     */
    public function getAddReviewLink()
    {
        return $this->_rootElement->find($this->addReviewLinkSelector);
    }

    /**
     * Click on add review link.
     *
     * @return void
     */
    public function clickAddReviewLink()
    {
        $reviewLink = $this->getAddReviewLink();
        if ($reviewLink->isVisible()) {
            $reviewLink->click();
        }
    }

    /**
     * Get view review link
     *
     * @return ElementInterface
     */
    public function getViewReviewLink()
    {
        return $this->_rootElement->find($this->viewReviewLinkSelector);
    }
}
