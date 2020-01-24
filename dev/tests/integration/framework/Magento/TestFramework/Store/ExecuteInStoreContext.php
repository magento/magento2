<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Store;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Execute operation in specified store
 */
class ExecuteInStoreContext
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Execute callback in store context
     *
     * @param callable $method
     * @param array $arguments
     * @param null|string|bool|int|StoreInterface $store
     * @return void
     */
    public function execute(callable $method, array $arguments, $store = 'default'): void
    {
        $currentStore = $this->storeManager->getStore();
        try {
            $this->storeManager->setCurrentStore($store);
            $method(...$arguments);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }
}
