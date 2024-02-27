<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;

class EmulateStore
{

    /**
     * @param StoreManager $storeManager
     */
    public function __construct(
        private StoreManager $storeManager
    ) {
    }

    /**
     * Execute callable with emulated store
     *
     * @param int $storeId
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function execute(int $storeId, callable $callable, array $args = []): mixed
    {
        $currentStoreId = (int)$this->storeManager->getStore()->getId();
        if ($currentStoreId !== $storeId) {
            $this->storeManager->setCurrentStore($storeId);
        }
        $result = $callable(...$args);
        if ($currentStoreId !== $storeId) {
            $this->storeManager->setCurrentStore($currentStoreId);
        }
        return $result;
    }
}
