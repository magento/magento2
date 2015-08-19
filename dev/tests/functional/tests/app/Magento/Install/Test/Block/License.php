<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * License block.
 */
class License extends Block
{
    /**
     * 'Back' button.
     *
     * @var string
     */
    protected $back = '[ng-click="nextState()"]';

    /**
     * License text.
     *
     * @var string
     */
    protected $license = '.license-text';

    /**
     * Click on 'Back' button.
     *
     * @return void
     */
    public function clickBack()
    {
        $this->_rootElement->find($this->back, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get license text.
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->_rootElement->find($this->license, Locator::SELECTOR_CSS)->getText();
    }
}
