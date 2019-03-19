<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store;

use Magento\Mtf\Client\Locator;
use Magento\Store\Test\Fixture\Store;
use Magento\Store\Test\Fixture\StoreGroup;
use Magento\Store\Test\Fixture\Website;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Adminhtml Store View management grid.
 */
class StoreGrid extends DataGrid
{
    /**
     * Secondary part of row locator template for getRow() method
     *
     * @var string
     */
    protected $rowTemplate = 'td[div[*[contains(.,normalize-space("%s"))]]]';

    /**
     * Secondary part of row locator template for getRow() method with strict option
     *
     * @var string
     */
    protected $rowTemplateStrict = 'td[div[*[text()[normalize-space()="%s"]]]]';

    /**
     * Locator value for opening needed row.
     *
     * @var string
     */
    protected $editLink = '[href*="editStore"]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'store_title' => [
            'selector' => '[name="store_title"]',
        ],
        'group_title' => [
            'selector' => '[name="group_title"]',
        ],
        'website_title' => [
            'selector' => '[name="name"]',
        ],
    ];

    /**
     * Store title format for XPATH.
     *
     * @var string
     */
    protected $titleFormat = '//a[.="%s"]';

    /**
     * Store name link selector.
     *
     * @var string
     */
    protected $storeName = '//a[.="%s"]';

    /**
     * Check if store exists.
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
     * Check if website exists.
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
     * Search and open appropriate Website.
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
     * Search and open appropriate Website by name.
     *
     * @param string $websiteName
     * @return void
     */
    public function searchAndOpenWebsiteByName($websiteName)
    {
        $this->search(['website_title' => $websiteName]);
        $this->_rootElement->find(sprintf($this->storeName, $websiteName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Search and open appropriate Store View.
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
     * Search and open appropriate Store.
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
