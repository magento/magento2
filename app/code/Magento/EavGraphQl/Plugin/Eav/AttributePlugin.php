<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Plugin\Eav;

use Magento\Eav\Model\Entity\Attribute;

/**
 * EAV plugin runs page cache clean and provides proper EAV identities.
 */
class AttributePlugin
{
    /**
     * Clean cache by relevant tags after entity save.
     *
     * @param Attribute $subject
     * @param array $result
     *
     * @return string[]
     */
    public function afterGetIdentities(Attribute $subject, array $result): array
    {
        return array_merge(
            $result,
            [
                sprintf(
                    "%s_%s",
                    Attribute::CACHE_TAG,
                    $subject->getAttributeCode()
                )
            ]
        );
    }
}
