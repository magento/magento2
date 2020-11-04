<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute\Source;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Can provide entity-specific options for an attribute.
 */
interface SpecificSourceInterface extends SourceInterface
{
    /**
     * List of options specific to an entity.
     *
     * Same format as for "getAllOptions".
     * Will be called instead of "getAllOptions".
     *
     * @param CustomAttributesDataInterface $entity
     * @return array
     */
    public function getOptionsFor(CustomAttributesDataInterface $entity): array;
}
