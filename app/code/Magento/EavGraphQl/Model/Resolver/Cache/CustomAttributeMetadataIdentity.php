<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Cache;

use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Cache identity provider for custom attribute metadata query results.
 */
class CustomAttributeMetadataIdentity implements IdentityInterface
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
                        "%s_%s_%s",
                        EavAttribute::CACHE_TAG,
                        $item['entity_type'],
                        $item['attribute_code']
                    );
                }
            }
        } else {
            return [];
        }
        return $identities;
    }
}
