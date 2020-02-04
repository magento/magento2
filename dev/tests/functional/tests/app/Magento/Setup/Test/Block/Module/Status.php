<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Module;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Status
 *
 * Contains action to manipulate with Module's actions.
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
     *
     * @return void
     */
    public function clickDisable()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Click en Enable element.
     *
     * @return void
     */
    public function clickEnable()
    {
        $this->_rootElement->find($this->button, Locator::SELECTOR_CSS)->click();
    }
}
