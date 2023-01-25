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
    const CACHE_TAG = 'gql_store_config';

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData) ? [] : [self::CACHE_TAG];
    }
}
