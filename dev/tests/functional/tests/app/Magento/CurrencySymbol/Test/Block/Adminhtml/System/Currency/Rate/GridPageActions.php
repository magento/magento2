<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System\Currency\Rate;

use Magento\Backend\Test\Block\PageActions;

/**
 * Grid page actions on the SystemCurrencyIndex page.
 */
class GridPageActions extends PageActions
{
    /**
     * Import button locator.
     *
     * @var string
     */
    protected $importButton = '[data-ui-id$="import-button"]';

    /**
     * Message block css selector.
     *
     * @var string
     */
    protected $message = '#messages';

    /**
     * Click Import button.
     *
     * @throws \Exception
     * @return void
     */
    public function clickImportButton()
    {
        $this->_rootElement->find($this->importButton)->click();

        //Wait message
        $browser = $this->browser;
        $selector = $this->message;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $message = $browser->find($selector);
                return $message->isVisible() ? true : null;
            }
        );
    }
}
