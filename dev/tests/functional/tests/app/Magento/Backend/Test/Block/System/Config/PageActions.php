<?php
/**
 * Config actions block
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Config;

use Magento\Backend\Test\Block\FormPageActions as AbstractPageActions;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Client\Locator;

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
    protected $scopeSelector = '.store-switcher .actions.dropdown';

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

        return $this;
    }

    /**
     * Check if store is visible in scope dropdown
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
