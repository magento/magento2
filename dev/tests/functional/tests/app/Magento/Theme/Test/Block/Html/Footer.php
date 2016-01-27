<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Block\Html;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Store\Test\Fixture\Store;

/**
 * Footer block
 * CmsIndex page Footer block
 */
class Footer extends Block
{
    /**
     * Locator value for correspondent link.
     *
     * @var string
     */
    protected $linkSelector = '//*[contains(@class, "links")]//a[contains(text(), "%s")]';

    /**
     * Locator value for variable.
     *
     * @var string
     */
    protected $variableSelector = './/ul[contains(@class, "links")]/*[text()="%s"]';

    /**
     * Locator value for "Store group" dropdown.
     *
     * @var string
     */
    protected $storeGroupDropdown = '.switcher.store';

    /**
     * Locator value for "Store group" switcher.
     *
     * @var string
     */
    protected $storeGroupSwitch = '[data-toggle="dropdown"]';

    /**
     * Locator value for correspondent Store group.
     *
     * @var string
     */
    protected $storeGroupSelector = './/a[contains(.,"%s")]';

    /**
     * Locator value for "Advanced Search" link.
     *
     * @var string
     */
    protected $advancedSearchSelector = '[data-action="advanced-search"]';

    /**
     * Click on link by its title.
     *
     * @param string $linkName
     * @return void
     * @throws \Exception
     */
    public function clickLink($linkName)
    {
        $link = $this->_rootElement->find(sprintf($this->linkSelector, $linkName), Locator::SELECTOR_XPATH);
        if (!$link->isVisible()) {
            throw new \Exception(sprintf('"%s" link is not visible', $linkName));
        }
        $link->click();
    }

    /**
     * Check is link is visible.
     *
     * @param string $linkName
     * @return bool
     */
    public function isLinkVisible($linkName)
    {
        return $this->_rootElement->find(sprintf($this->linkSelector, $linkName), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Check Variable visibility by html value.
     *
     * @param string $htmlValue
     * @return bool
     */
    public function checkVariable($htmlValue)
    {
        return $this->_rootElement->find(
            sprintf($this->variableSelector, $htmlValue),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Select Store group.
     *
     * @param Store $store
     * @return void
     */
    public function selectStoreGroup(Store $store)
    {
        $storeGroupName = explode("/", $store->getGroupId())[1];
        $storeGroup = $this->_rootElement->find(
            sprintf($this->storeGroupSelector, $storeGroupName),
            Locator::SELECTOR_XPATH
        );
        if (!$storeGroup->isVisible()) {
            $this->_rootElement->find($this->storeGroupSwitch)->click();
        }

        $storeGroup->click();
    }

    /**
     * Check if correspondent "Store" is present in "Store" swither or not.
     *
     * @param Store $store
     * @return bool
     */
    public function isStoreGroupVisible(Store $store)
    {
        $storeGroupName = explode("/", $store->getGroupId())[1];
        $this->_rootElement->find($this->storeGroupSwitch)->click();
        return $this->_rootElement->find(
            sprintf($this->storeGroupSelector, $storeGroupName),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Check if "Store" switcher is visible or not.
     *
     * @return bool
     */
    public function isStoreGroupSwitcherVisible()
    {
        return $this->_rootElement->find($this->storeGroupSwitch)->isVisible();
    }

    /**
     * Open Advanced Search.
     *
     * @return void
     */
    public function openAdvancedSearch()
    {
        $this->_rootElement->find($this->advancedSearchSelector)->click();
    }
}
