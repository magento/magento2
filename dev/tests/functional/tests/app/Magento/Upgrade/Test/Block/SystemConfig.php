<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Home block.
 */
class SystemConfig extends Block
{
    /**
     * @var string
     */
    protected $systemConfig = '.setup-home-item-configuration';

    /**
     * Click on 'Agree and Set up Magento' button.
     *
     * @return void
     */
    public function clickSystemConfig()
    {
        $this->_rootElement->find($this->systemConfig, Locator::SELECTOR_CSS)->click();
    }
}
