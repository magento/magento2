<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Data Option block.
 */
class DataOption extends Block
{
    /**
     * "Next" button.
     *
     * @var string
     */
    protected $nextState = "[ng-click*='nextState']";

    /**
     * Click "Next" button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->nextState, Locator::SELECTOR_CSS)->click();
    }
}
