<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Success Message block.
 */
class SuccessMessage extends Block
{
    /**
     * @var string
     */
    protected $successMessage = 'content-success';

    /**
     * @return string
     */
    public function getUpdaterStatus()
    {
        $this->waitForElementVisible($this->successMessage, Locator::SELECTOR_CLASS_NAME);
        return $this->_rootElement->find($this->successMessage, Locator::SELECTOR_CLASS_NAME)->getText();
    }
}
