<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Menu block on Cybersource sandbox.
 */
class SearchForm extends Block
{
    /**
     * Search button selector.
     *
     * @var string
     */
    private $searchButton = '[type=submit]';

    /**
     * Got It button selector.
     * This button is located in notification window which may appear immediately after login.
     *
     * @var string
     */
    private $settlementDate = '[name="StartBatch"]';

    /**
     * Search for all unsettled transactions.
     *
     * @return void
     */
    public function search()
    {
        $this->_rootElement->find($this->settlementDate, Locator::SELECTOR_CSS, 'select')->setValue('Unsettled');
        $this->browser->find($this->searchButton)->click();
    }
}
