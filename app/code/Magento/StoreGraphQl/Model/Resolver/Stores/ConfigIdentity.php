<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Stores;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity as StoreConfigIdentity;

class ConfigIdentity implements IdentityInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        foreach ($resolvedData as $storeConfig) {
            $ids[] = sprintf('%s_%s', StoreConfigIdentity::CACHE_TAG, $storeConfig['id']);
        }

        return empty($ids) ? [] : array_merge([StoreConfigIdentity::CACHE_TAG], $ids);
    }
}
