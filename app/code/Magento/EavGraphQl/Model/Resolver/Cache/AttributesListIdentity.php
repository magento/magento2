<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver\Cache;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Cache identity provider for attributes list query results.
 */
class AttributesListIdentity implements IdentityInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        if (empty($resolvedData['entity_type']) || $resolvedData['entity_type'] === "") {
            return [];
        }

        $identities = [
            Config::ENTITIES_CACHE_ID . "_" . $resolvedData['entity_type'] . "_ENTITY"
        ];

        if (empty($resolvedData['items']) || !is_array($resolvedData['items'][0])) {
            return $identities;
        }

        foreach ($resolvedData['items'] as $item) {
            if ($item['attribute'] instanceof AttributeInterface) {
                $identities[] = sprintf(
                    "%s_%s",
                    Attribute::CACHE_TAG,
                    $item['attribute']->getAttributeId()
                );
            }
        }
        return $identities;
    }
}
