<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * System Config block.
 */
class SystemConfig extends Block
{
    /**
     * @var string
     */
    protected $systemConfig = '.setup-home-item-configuration';

    /**
     * Click on 'System Configuration' button.
     *
     * @return void
     */
    public function clickSystemConfig()
    {
        $this->_rootElement->find($this->systemConfig, Locator::SELECTOR_CSS)->click();
    }
}
