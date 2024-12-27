<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Cache;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Cache identity provider for attributes form query
 */
class AttributesFormIdentity implements IdentityInterface
{
    public const CACHE_TAG = 'EAV_FORM';
    /**
     * @inheritDoc
     */
    public function getIdentities(array $resolvedData): array
    {
        if (empty($resolvedData['items'])) {
            return [];
        }

        $identities = [];
        
        if ($resolvedData['formCode'] !== '') {
            $identities[] = sprintf(
                "%s_%s_FORM",
                self::CACHE_TAG,
                $resolvedData['formCode'] ?? ''
            );
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
