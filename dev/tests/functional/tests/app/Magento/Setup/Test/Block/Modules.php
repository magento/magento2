<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Modules
 */
class Modules extends Block
{
    /**
     * @var string
     */
    protected $upgrade = ".setup-home-item-component";

    public function clickModules()
    {
        $this->_rootElement->find($this->upgrade, Locator::SELECTOR_CSS)->click();
    }
}