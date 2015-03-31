<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System\Currency;

use Magento\Backend\Test\Block\PageActions;

/**
 * Class MainPageActions
 * Main page actions on the SystemCurrencyIndex page
 */
class MainPageActions extends PageActions
{
    /**
     * "Save Currency Rates" button locator
     *
     * @var string
     */
    protected $saveCurrentRate = '[data-ui-id="page-actions-toolbar-save-button"]';

    /**
     * Save Currency Rates
     *
     * @return void
     */
    public function saveCurrentRate()
    {
        $this->_rootElement->find($this->saveCurrentRate)->click();
    }
}
