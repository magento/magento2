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
        $storeGroupIds = [];
        $store = null;
        $storeGroupCount = 0;
        foreach ($resolvedData as $storeConfig) {
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, $storeConfig['id']);
            if ($storeGroupCount < 2) {
                try {
                    // Record store groups
                    $store = $this->storeManager->getStore($storeConfig['id']);
                    $storeGroupId = $store->getStoreGroupId();
                    if ($storeGroupId !== null && !in_array($storeGroupId, $storeGroupIds)) {
                        $storeGroupIds[] = $storeGroupId;
                        $storeGroupCount ++;
                    }
                } catch (NoSuchEntityException $e) {
                    // Do nothing
                    ;
                }
            }
        }
        if ($storeGroupCount > 1) { // the resolved stores for any store groups in a website
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, 'website_' . $store->getWebsiteId());
        } elseif ($storeGroupCount == 1) { // the resolved stores for a particular store group in a website
            $ids[] = sprintf(
                '%s_%s',
                StoreConfigIdentity::CACHE_TAG,
                'website_' . $store->getWebsiteId() . 'group_' . $storeGroupIds[0]
            );
        }

        return empty($ids) ? [] : array_merge([StoreConfigIdentity::CACHE_TAG], $ids);
    }
}
