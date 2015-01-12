<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Block\Html;

use Magento\Store\Test\Fixture\Store;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Footer block
 * CmsIndex page footer block
 */
class Footer extends Block
{
    /**
     * Link selector
     *
     * @var string
     */
    protected $linkSelector = '//*[contains(@class, "links")]//a[contains(text(), "%s")]';

    /**
     * Variable selector
     *
     * @var string
     */
    protected $variableSelector = './/ul[contains(@class, "links")]/*[text()="%s"]';

    /**
     * Store group dropdown selector
     *
     * @var string
     */
    protected $storeGroupDropdown = '.switcher.store';

    /**
     * Store Group switch selector
     *
     * @var string
     */
    protected $storeGroupSwitch = '[data-toggle="dropdown"]';

    /**
     * Store group selector
     *
     * @var string
     */
    protected $storeGroupSelector = './/a[contains(.,"%s")]';

    /**
     * Click on link by name
     *
     * @param string $linkName
     * @return \Mtf\Client\Element
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
     * Check Variable visibility by html value
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
     * Select store group
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
     * Check if store visible in dropdown
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
     * Check if store group switcher is visible
     *
     * @return bool
     */
    public function isStoreGroupSwitcherVisible()
    {
        return $this->_rootElement->find($this->storeGroupSwitch)->isVisible();
    }
}
