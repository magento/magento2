<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\Page;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Header block
 *
 */
class Header extends Block
{
    /**
     * Selector for Account Avatar
     *
     * @var string
     */
    protected $adminAccountLink = '.admin-user-account';

    /**
     * Selector for Log Out Link
     *
     * @var string
     */
    protected $signOutLink = '.account-signout';

    /**
     * Selector for Search Link
     *
     * @var string
     */
    protected $searchSelector = '#form-search';

    /**
     * Log out Admin User
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
     * Get admin account link visibility
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_rootElement->find($this->adminAccountLink)->isVisible();
    }

    /**
     * Search the query text
     *
     * @param string $query
     * @return void
     */
    public function search($query)
    {
        /** @var \Mtf\Client\Driver\Selenium\Element\GlobalSearchElement $search */
        $search = $this->_rootElement->find($this->searchSelector, Locator::SELECTOR_CSS, 'globalSearch');
        $search->setValue($query);
    }

    /**
     * Is search result is visible in suggestion dropdown
     *
     * @param string $query
     * @return bool
     */
    public function isSearchResultVisible($query)
    {
        /** @var \Mtf\Client\Driver\Selenium\Element\GlobalSearchElement $search */
        $search = $this->_rootElement->find($this->searchSelector, Locator::SELECTOR_CSS, 'globalSearch');
        return $search->isExistValueInSearchResult($query);
    }
}
