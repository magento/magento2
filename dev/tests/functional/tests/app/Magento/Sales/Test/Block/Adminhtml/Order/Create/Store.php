<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;

/**
 * Class Store
 * Adminhtml sales order create select store block
 *
 */
class Store extends Block
{
    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Locator value for specified Store control.
     *
     * @var string
     */
    protected $store = '//label[contains(., "%s")]/preceding-sibling::*';

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Select store view for order based on Order fixture
     *
     * @param StoreFixture|null $fixture
     */
    public function selectStoreView(StoreFixture $fixture = null)
    {
        if (!$this->isVisible()) {
            return;
        }
        $storeName = $fixture == null ? 'Default Store View' : $fixture->getName();
        $selector = sprintf($this->store, $storeName);
        $this->_rootElement->find($selector, Locator::SELECTOR_XPATH, 'checkbox')->setValue('Yes');
        $this->getTemplateBlock()->waitLoader();
    }
}
