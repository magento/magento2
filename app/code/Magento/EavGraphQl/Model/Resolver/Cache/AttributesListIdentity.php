<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Cache;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Cache identity provider for attributes list query results.
 */
class AttributesListIdentity implements IdentityInterface
{
    public const CACHE_TAG = 'ATTRIBUTES_LIST';

    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        if (empty($resolvedData['items'])) {
            return [];
        }

        if (!is_array($resolvedData['items'][0])) {
            return [];
        }

        return [sprintf(
            "%s_%s",
            self::CACHE_TAG,
            $resolvedData['items'][0]['entity_type']
        )];
    }
}
