<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\Filter\Clause;

/**
 * Class that hold relation between entities through fields
 */
class ReferenceType
{
    /**
     * @var ReferenceType
     */
    private $referenceType;

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var string
     */
    private $linkField;

    /**
     * @param string $entityType
     * @param string|null $linkField
     * @param ReferenceType|null $referenceType
     */
    public function __construct(
        string $entityType,
        string $linkField = null,
        ReferenceType $referenceType = null
    ) {
        $this->entityType = $entityType;
        $this->linkField = $linkField;
        $this->referenceType = $referenceType;
    }

    /**
     * Get the reference type
     *
     * @return ReferenceType
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * Get the linked field as string
     *
     * @return string
     */
    public function getLinkField()
    {
        return $this->linkField;
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }
}
