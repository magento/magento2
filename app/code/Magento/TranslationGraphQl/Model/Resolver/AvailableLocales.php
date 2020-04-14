<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TranslationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;

/**
 * Retrieval of available locales configured for all store views
 */
class AvailableLocales implements ResolverInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var StoreConfigManagerInterface
     */
    private $storeConfigManager;

    /**
     * Translations constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreConfigManagerInterface $configManager
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreConfigManagerInterface $configManager
    ) {
        $this->storeRepository = $storeRepository;
        $this->storeConfigManager = $configManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return [
            'items' => $this->getLocales()
        ];
    }

    /**
     * Get all locales configured
     *
     * @return array
     */
    private function getLocales(): array
    {
        $locales = [];
        $stores = $this->storeRepository->getList();

        $storeCodes = [];
        /** @var WebsiteInterface $website */
        foreach ($stores as $store) {
            $storeCodes[] = $store->getCode();
        }

        $storeConfigs = $this->storeConfigManager->getStoreConfigs($storeCodes);
        foreach ($storeConfigs as $storeConfig) {
            $locale = $storeConfig->getLocale();
            $locales[$locale] = [
                'locale' => $locale
            ];
        }

        return $locales;
    }
}
