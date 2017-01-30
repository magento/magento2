<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Home block.
 */
class Home extends Block
{
    /**
     * @var string
     */
    protected $systemUpgrade = '.setup-home-item-upgrade';

    /**
     * Click on 'System Upgrade' button.
     *
     * @return void
     */
    public function clickSystemUpgrade()
    {
        $this->_rootElement->find($this->systemUpgrade, Locator::SELECTOR_CSS)->click();
    }
}
