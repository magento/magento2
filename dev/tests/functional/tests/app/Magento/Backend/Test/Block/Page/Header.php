<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
            $this->browser->waitUntil(function () {
                return $this->browser->find('[data-role="spinner"]')->isVisible() ? null : true;
            });
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

    /**
     * Is admin search preview visible in suggestion dropdown.
     *
     * @param string $query
     * @param string $type
     * @return bool
     */
    public function isAdminSearchPreviewVisible($query, $type)
    {
        /** @var GlobalsearchElement $search */
        $search = $this->_rootElement->find('searchPreview' . $type, Locator::SELECTOR_ID);
        return $search->getText() === $query;
    }

    /**
     * Navigate to grid of specified type
     *
     * @param string $type
     */
    public function navigateToGrid($type)
    {
        $this->_rootElement->find('searchPreview' . $type, Locator::SELECTOR_ID)->click();
    }
}
