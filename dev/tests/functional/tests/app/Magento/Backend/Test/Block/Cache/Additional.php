<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Cache;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Additional Cache Management block.
 */
class Additional extends Block
{
    public function click($selector, $strategy = Locator::SELECTOR_XPATH)
    {
        $this->_rootElement->find($selector, $strategy)->click();
    }
}
