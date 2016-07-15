<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Module;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Status
 */
class Status extends Block
{
    /**
     * Button selector.
     *
     * @var string
     */
    protected $button = '.btn-large';

    /**
     * Click on Disable Element.
     */
    public function clickDisable()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click en Enable element.
     */
    public function clickEnable()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }
}
