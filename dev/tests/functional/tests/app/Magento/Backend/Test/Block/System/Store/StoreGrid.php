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

namespace Magento\Backend\Test\Block\System\Store;

use Mtf\Client\Element\Locator;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Store\Test\Fixture\Website;
use Magento\Store\Test\Fixture\Store;
use Magento\Backend\Test\Block\Widget\Grid as GridInterface;

/**
 * Class StoreGrid
 * Adminhtml Store View management grid
 */
class StoreGrid extends GridInterface
{
    /**
     * Locator value for opening needed row
     *
     * @var string
     */
    protected $editLink = 'td[data-column="store_title"] > a';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'store_title' => [
            'selector' => '#storeGrid_filter_store_title',
        ],
        'group_title' => [
            'selector' => '#storeGrid_filter_group_title'
        ],
        'website_title' => [
            'selector' => '#storeGrid_filter_website_title'
        ]
    ];

    /**
     * Store title format for XPATH
     *
     * @var string
     */
    protected $titleFormat = '//td[a[.="%s"]]';

    /**
     * Store name link selector
     *
     * @var string
     */
    protected $storeName = '//a[.="%s"]';

    /**
     * Check if store exists
     *
     * @param string $title
     * @return bool
     */
    public function isStoreExists($title)
    {
        $element = $this->_rootElement->find(sprintf($this->titleFormat, $title), Locator::SELECTOR_XPATH);
        return $element->isVisible();
    }

    /**
     * Check if website exists
     *
     * @param Website $website
     * @return bool
     */
    public function isWebsiteExists($website)
    {
        return $this->_rootElement->find(sprintf($this->titleFormat, $website->getName()), Locator::SELECTOR_XPATH)
            ->isVisible();
    }

    /**
     * Search and open appropriate Website
     *
     * @param Website $website
     * @return void
     */
    public function searchAndOpenWebsite(Website $website)
    {
        $websiteName = $website->getName();
        $this->search(['website_title' => $websiteName]);
        $this->_rootElement->find(sprintf($this->storeName, $websiteName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Search and open appropriate Store View
     *
     * @param Store $store
     * @return void
     */
    public function searchAndOpenStore(Store $store)
    {
        $storeName = $store->getName();
        $this->search(['store_title' => $storeName]);
        $this->_rootElement->find(sprintf($this->storeName, $storeName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Search and open appropriate Store
     *
     * @param StoreGroup $storeGroup
     * @return void
     */
    public function searchAndOpenStoreGroup(StoreGroup $storeGroup)
    {
        $storeGroupName = $storeGroup->getName();
        $this->search(['group_title' => $storeGroupName]);
        $this->_rootElement->find(sprintf($this->storeName, $storeGroupName), Locator::SELECTOR_XPATH)->click();
    }
}
