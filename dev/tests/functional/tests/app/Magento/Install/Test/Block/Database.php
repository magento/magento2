<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Database form.
 */
class Database extends Form
{
    /**
     * 'Test connection successful.' message.
     *
     * @var string
     */
    protected $successConnectionMessage = ".text-success";

    /**
     * 'Next' button.
     *
     * @var string
     */
    protected $next = "[ng-click*='testConnection']";

    /**
     * Get 'Test connection successful.' message.
     *
     * @return string
     */
    public function getSuccessConnectionMessage()
    {
        return $this->_rootElement->find($this->successConnectionMessage, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Click on 'Next' button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }
}
