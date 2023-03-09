<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Stores;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity as StoreConfigIdentity;

class ConfigIdentity implements IdentityInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $storeGroups = [];
        $store = null;
        foreach ($resolvedData as $storeConfig) {
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, $storeConfig['id']);
            try {
                // Record store groups
                $store = $this->storeManager->getStore($storeConfig['id']);
                $storeGroupId = $store->getStoreGroupId();
                if ($storeGroupId !== null) {
                    $storeGroups[$storeGroupId] = true;
                }
            } catch (NoSuchEntityException $e) {
                // Do nothing
                ;
            }
        }
        $storeGroupCount = count($storeGroups);
        if ($storeGroupCount > 1 && $store !== null) {
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, 'website_' . $store->getWebsiteId());
        } elseif ($storeGroupCount == 1 && $store !== null) {
            $ids[] = sprintf(
                '%s_%s',
                StoreConfigIdentity::CACHE_TAG,
                'website_' . $store->getWebsiteId() . 'group_' . array_keys($storeGroups)[0]
            );
        }

        return empty($ids) ? [] : array_merge([StoreConfigIdentity::CACHE_TAG], $ids);
    }
}
