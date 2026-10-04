<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Cache;

use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Cache identity provider for custom attribute metadata query results.
 */
class CustomAttributeMetadataV2Identity implements IdentityInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $identities = [];
        if (isset($resolvedData['items']) && !empty($resolvedData['items'])) {
            foreach ($resolvedData['items'] as $item) {
                if (is_array($item)) {
                    $identities[] = sprintf(
                        "%s_%s",
                        EavAttribute::CACHE_TAG,
                        $item['id']
                    );
                }
            }
        } else {
            return [];
        }
        return $identities;
    }
}
