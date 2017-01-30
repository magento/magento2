<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Cache actions block.
 */
class Cache extends Block
{
    /**
     * 'Flush Magento Cache' button.
     *
     * @var string
     */
    protected $flushMagentoCacheButton = '[data-ui-id="adminhtml-cache-container-flush-magento-button"]';

    /**
     * 'Flush Cache Storage' button.
     *
     * @var string
     */
    protected $flushCacheStorageButton = '[data-ui-id="adminhtml-cache-container-flush-system-button"]';

    /**
     * Selector for messages block.
     *
     * @var string
     */
    protected $messagesSelector = '//ancestor::div//div[@id="messages"]';

    /**
     * Messages texts.
     *
     * @var array
     */
    protected $messagesText = [
        'cache_storage_flushed' => 'You flushed the cache storage.',
        'cache_magento_flushed' => 'The Magento cache storage has been flushed.',
    ];

    /**
     * Flush magento cache.
     */
    public function flushMagentoCache()
    {
        $this->_rootElement->find($this->flushMagentoCacheButton)->click();
    }

    /**
     * Flush cache storage.
     */
    public function flushCacheStorage()
    {
        $this->_rootElement->find($this->flushCacheStorageButton)->click();
    }

    /**
     * Is storage cache flushed successfully.
     *
     * @return bool
     */
    public function isStorageCacheFlushed()
    {
        return $this->getMessagesBlock()->getSuccessMessage() == $this->messagesText['cache_storage_flushed'];
    }

    /**
     * Is magento cache flushed successfully.
     *
     * @return bool
     */
    public function isMagentoCacheFlushed()
    {
        return $this->getMessagesBlock()->getSuccessMessage() == $this->messagesText['cache_magento_flushed'];
    }

    /**
     * Get messages block.
     *
     * @return \Magento\Backend\Test\Block\Messages
     */
    protected function getMessagesBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Messages',
            ['element' => $this->_rootElement->find($this->messagesSelector, Locator::SELECTOR_XPATH)]
        );
    }
}
