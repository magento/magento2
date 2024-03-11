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

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class SetCustomerStore
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(private StoreManagerInterface $storeManager)
    {
    }

    /**
     * Set store ID for the current customer.
     *
     * @param array|null $requestData
     * @return void
     */
    public function setStore(array|null $requestData = null): void
    {
        $websiteId = $requestData[CustomerInterface::WEBSITE_ID] ?? null;
        try {
            $website = $this->storeManager->getWebsite($websiteId);
            $storeId = $website ? current($website->getStoreIds()) : null;
        } catch (LocalizedException $e) {
            $storeId = null;
        }
        if (!$storeId) {
            $storeId = $requestData[CustomerInterface::STORE_ID] ?? null;
        }

        $this->storeManager->setCurrentStore($storeId);
    }
}
