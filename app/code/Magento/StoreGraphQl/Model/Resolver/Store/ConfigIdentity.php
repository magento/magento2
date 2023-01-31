<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver\Store;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class ConfigIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'gql_store_config';

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $data["id"] =  empty($resolvedData) ? [] : $resolvedData["id"];
        $ids =  empty($resolvedData) ?
            [] : array_merge([self::CACHE_TAG], array_map(function ($key) {
                return sprintf('%s_%s', self::CACHE_TAG, $key);
            }, $data));
        return $ids;
    }
}
