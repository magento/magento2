<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Resolver\Currency;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'gql_currency';

    public function getIdentities(array $resolvedData): array
    {
        return [self::CACHE_TAG];
    }
}
