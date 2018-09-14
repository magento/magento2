<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;

/**
 * StoreConfig field data provider, used for GraphQL request processing.
 */
class StoreConfigDataProvider
{
    /**
     * @var StoreConfigManagerInterface
     */
    private $storeConfigManager;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @param StoreConfigManagerInterface $storeConfigManager
     * @param StoreResolverInterface $storeResolver
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        StoreConfigManagerInterface $storeConfigManager,
        StoreResolverInterface $storeResolver,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeConfigManager = $storeConfigManager;
        $this->storeResolver = $storeResolver;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get store config for current store
     *
     * @return array
     */
    public function getStoreConfig() : array
    {
        $storeId = $this->storeResolver->getCurrentStoreId();
        $store = $this->storeRepository->getById($storeId);
        $storeConfig = current($this->storeConfigManager->getStoreConfigs([$store->getCode()]));

        $storeConfigData = [
            'id' => $storeConfig->getId(),
            'code' => $storeConfig->getCode(),
            'website_id' => $storeConfig->getWebsiteId(),
            'locale' => $storeConfig->getLocale(),
            'base_currency_code' => $storeConfig->getBaseCurrencyCode(),
            'default_display_currency_code' => $storeConfig->getDefaultDisplayCurrencyCode(),
            'timezone' => $storeConfig->getTimezone(),
            'weight_unit' => $storeConfig->getWeightUnit(),
            'base_url' => $storeConfig->getBaseUrl(),
            'base_link_url' => $storeConfig->getBaseLinkUrl(),
            'base_static_url' => $storeConfig->getSecureBaseStaticUrl(),
            'base_media_url' => $storeConfig->getBaseMediaUrl(),
            'secure_base_url' => $storeConfig->getSecureBaseUrl(),
            'secure_base_link_url' => $storeConfig->getSecureBaseLinkUrl(),
            'secure_base_static_url' => $storeConfig->getSecureBaseStaticUrl(),
            'secure_base_media_url' => $storeConfig->getSecureBaseMediaUrl()
        ];
        return $storeConfigData;
    }
}
