<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Module;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Disable
 */
class Disable extends Block
{
    protected $button = '.btn-large';

    public function clickDisable()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }
}