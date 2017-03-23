<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;

/**
 * New or old review order block on PayPal side.
 */
class ExpressMainReview extends Block
{
    /**
     * Express Review Block selector.
     *
     * @var string
     */
    protected $expressReview = '#memberReview';

    /**
     * Determines whether new review block or old is shown.
     *
     * @return \Magento\Paypal\Test\Block\Sandbox\ExpressReview|\Magento\Paypal\Test\Block\Sandbox\ExpressOldReview
     */
    public function getReviewBlock()
    {
        if ($this->_rootElement->find($this->expressReview)->isVisible()) {
            return $this->blockFactory->create(
                \Magento\Paypal\Test\Block\Sandbox\ExpressReview::class,
                ['element' => $this->_rootElement]
            );
        }
        return $this->blockFactory->create(
            \Magento\Paypal\Test\Block\Sandbox\ExpressOldReview::class,
            ['element' => $this->_rootElement]
        );
    }
}
