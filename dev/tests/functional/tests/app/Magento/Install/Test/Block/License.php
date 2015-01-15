<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

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
    protected $back = '.btn.btn-primary';

    /**
     * License text.
     *
     * @var string
     */
    protected $license = '.container.ng-scope';

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
