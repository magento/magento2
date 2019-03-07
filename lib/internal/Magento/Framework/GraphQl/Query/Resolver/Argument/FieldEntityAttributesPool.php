<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument;

/**
 * Retrieves attributes for a field for the ast converter
 */
class FieldEntityAttributesPool
{
    /**
     * @var FieldEntityAttributesInterface[]
     */
    private $attributesInstances = [];

    /**
     * @param FieldEntityAttributesInterface[] $attributesInstances
     */
    public function __construct(
        array $attributesInstances = []
    ) {
        $this->attributesInstances = $attributesInstances;
    }

    /**
     * Get the attributes that can be filtered for based on the graphql field name that represents an entity
     *
     * @param string $fieldName
     * @return array
     * @throws \LogicException
     */
    public function getEntityAttributesForEntityFromField(string $fieldName) : array
    {
        if (isset($this->attributesInstances[$fieldName])) {
            return $this->attributesInstances[$fieldName]->getEntityAttributes();
        } else {
            throw new \LogicException(sprintf('There is no attribute class assigned to field %1', $fieldName));
        }
    }
}
