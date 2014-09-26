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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Theme\Test\Block\Html;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Store\Test\Fixture\Store;

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
    protected $variableSelector = './/div[contains(@class, "links")]/*[text()="%s"]';

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
