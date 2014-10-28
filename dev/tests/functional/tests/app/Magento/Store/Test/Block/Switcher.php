<?php
/**
 * Language switcher
 *
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
namespace Magento\Store\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Store\Test\Fixture\Store;

/**
 * Class Switcher
 * Store switcher block
 */
class Switcher extends Block
{
    /**
     * Dropdown button selector
     *
     * @var string
     */
    protected $dropDownButton = '#switcher-language-trigger';

    /**
     * StoreView selector
     *
     * @var string
     */
    protected $storeViewSelector = 'li.view-%s';

    /**
     * Select store
     *
     * @param string $name
     * @return void
     */
    public function selectStoreView($name)
    {
        if ($this->_rootElement->find($this->dropDownButton)->isVisible() && ($this->getStoreView() !== $name)) {
            $this->_rootElement->find($this->dropDownButton)->click();
            $this->_rootElement->find($name, Locator::SELECTOR_LINK_TEXT)->click();
        }
    }

    /**
     * Get store view
     *
     * @return string
     */
    public function getStoreView()
    {
        return $this->_rootElement->find($this->dropDownButton)->getText();
    }

    /**
     * Check is Store View Visible
     *
     * @param Store $store
     * @return bool
     */
    public function isStoreViewVisible($store)
    {
        $storeViewDropdown = $this->_rootElement->find($this->dropDownButton);

        $storeViewDropdown->click();
        $isStoreViewVisible = $this->_rootElement->find(sprintf($this->storeViewSelector, $store->getCode()))
            ->isVisible();
        $storeViewDropdown->click();
        return $isStoreViewVisible;
    }

    /**
     * Check if StoreView dropdown is visible
     *
     * @return bool
     */
    public function isStoreViewDropdownVisible()
    {
        return $this->_rootElement->find($this->dropDownButton)->isVisible();
    }
}
