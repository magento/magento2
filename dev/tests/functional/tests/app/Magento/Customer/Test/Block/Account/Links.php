<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Account;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Links
 * Links block on customer account page
 */
class Links extends Block
{
    /**
     * XPath locator for account navigation on customer page
     *
     * @var string
     */
    protected $menuItem = '//*[contains(@class,"item")]/a[contains(.,"%s")]';

    /**
     * Select link in menu
     *
     * @param string $link
     * @return void
     */
    public function openMenuItem($link)
    {
        $this->_rootElement->find(sprintf($this->menuItem, $link), Locator::SELECTOR_XPATH)->click();
    }
}
