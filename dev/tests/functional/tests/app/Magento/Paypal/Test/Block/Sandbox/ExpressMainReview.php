<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Old Express Review Block selector.
     *
     * @var string
     */
    protected $expressOldReview = '#stdpage';

    /**
     * Determines whether new review block or old is shown.
     *
     * @return \Magento\Paypal\Test\Block\Sandbox\ExpressReview|\Magento\Paypal\Test\Block\Sandbox\ExpressOldReview
     */
    public function getReviewBlock()
    {
        if ($this->_rootElement->find($this->expressReview)->isVisible()) {
            return $this->blockFactory->create(
                'Magento\Paypal\Test\Block\Sandbox\ExpressReview',
                ['element' => $this->_rootElement->find($this->expressReview)]
            );
        }
        return $this->blockFactory->create(
            'Magento\Paypal\Test\Block\Sandbox\ExpressOldReview',
            ['element' => $this->_rootElement->find($this->expressOldReview)]
        );
    }
}
