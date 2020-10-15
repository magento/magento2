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
     * @param null|string|bool|int|StoreInterface $store
     * @param callable $method
     * @param array $arguments
     * @return mixed
     */
    public function execute($store, callable $method, ...$arguments)
    {
        $storeCode = $store instanceof StoreInterface
            ? $store->getCode()
            : $this->storeManager->getStore($store)->getCode();
        $currentStore = $this->storeManager->getStore();

        try {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($storeCode);
            }

            return $method(...array_values($arguments));
        } finally {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($currentStore);
            }
        }
    }
}
