<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\GlobalsearchElement;

/**
 * Header block.
 */
class Header extends Block
{
    /**
     * Selector for Account Avatar.
     *
     * @var string
     */
    protected $adminAccountLink = '.admin-user-account-text';

    /**
     * Selector for Log Out Link.
     *
     * @var string
     */
    protected $signOutLink = '.account-signout';

    /**
     * Selector for Search Link.
     *
     * @var string
     */
    protected $searchSelector = '.search-global';

    /**
     * Log out Admin User.
     *
     * @return void
     */
    public function logOut()
    {
        if ($this->isLoggedIn()) {
            $this->_rootElement->find($this->adminAccountLink)->click();
            $this->_rootElement->find($this->signOutLink)->click();
            $this->waitForElementNotVisible($this->signOutLink);
        }
    }

    /**
     * Get admin account link visibility.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_rootElement->find($this->adminAccountLink)->isVisible();
    }

    /**
     * Search the query text.
     *
     * @param string $query
     * @return void
     */
    public function search($query)
    {
        /** @var GlobalsearchElement $search */
        $search = $this->_rootElement->find($this->searchSelector, Locator::SELECTOR_CSS, 'globalsearch');
        $search->setValue($query);
    }

    /**
     * Is search result is visible in suggestion dropdown.
     *
     * @param string $query
     * @return bool
     */
    public function isSearchResultVisible($query)
    {
        /** @var GlobalsearchElement $search */
        $search = $this->_rootElement->find($this->searchSelector, Locator::SELECTOR_CSS, 'globalsearch');
        return $search->isExistValueInSearchResult($query);
    }
}
