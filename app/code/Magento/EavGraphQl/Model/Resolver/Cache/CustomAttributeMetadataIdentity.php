<?php

namespace Magento\EavGraphQl\Model\Resolver\Cache;

use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Cache identity provider for custom attribute metadata query results.
 */
class CustomAttributeMetadataIdentity implements IdentityInterface
{
    /**
     * @inheirtdoc
     */
    public function getIdentities(array $resolvedData): array
    {
        $identities = [EavAttribute::CACHE_TAG];
        if (isset($resolvedData['items']) && !empty($resolvedData['items'])) {
            foreach ($resolvedData['items'] as $item) {
                $identities[] = sprintf(
                    "%s_%s",
                    EavAttribute::CACHE_TAG,
                    $item['attribute_code']
                );
            }
        } else {
            return [];
        }
        return $identities;
    }
}
