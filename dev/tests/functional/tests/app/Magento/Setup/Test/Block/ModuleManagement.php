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
class ModuleManagement extends Block
{
    /**
     * @var string
     */
    protected $moduleManagement = ".setup-home-item-component";

    public function clickModules()
    {
        $this->_rootElement->find($this->moduleManagement, Locator::SELECTOR_CSS)->click();
    }
}