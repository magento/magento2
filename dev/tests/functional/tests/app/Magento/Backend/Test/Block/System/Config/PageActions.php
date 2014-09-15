<?php
/**
 * Config actions block
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

namespace Magento\Backend\Test\Block\System\Config;

use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\FormPageActions as AbstractPageActions;
use Magento\Store\Test\Fixture\Store;

/**
 * Class PageActions
 * System config page action
 */
class PageActions extends AbstractPageActions
{
    /**
     * Scope CSS selector
     *
     * @var string
     */
    protected $scopeSelector = '.actions.dropdown';

    /**
     * Select store
     *
     * @param string $websiteScope
     * @return $this
     */
    public function selectStore($websiteScope)
    {
        $this->_rootElement->find($this->scopeSelector, Locator::SELECTOR_CSS, 'liselectstore')
            ->setValue($websiteScope);
        $this->_rootElement->acceptAlert();

        return $this;
    }

    /**
     * Check if store visible in scope dropdown
     *
     * @param Store $store
     * @return bool
     */
    public function isStoreVisible($store)
    {
        $storeViews = $this->_rootElement->find($this->scopeSelector, Locator::SELECTOR_CSS, 'liselectstore')
            ->getValues();
        return in_array($store->getGroupId() . "/" . $store->getName(), $storeViews);
    }
}
