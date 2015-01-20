<?php
/**
 * Config actions block
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Config;

use Magento\Backend\Test\Block\FormPageActions as AbstractPageActions;
use Magento\Store\Test\Fixture\Store;
use Mtf\Client\Element\Locator;

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
