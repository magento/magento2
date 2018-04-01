<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Clause;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see ReferenceType
 */
class ReferenceTypeFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create ReferenceType class
     *
     * @param string $entityType
     * @param string|null $linkField
     * @param ReferenceType|null $referenceType
     * @return ReferenceType
     */
    public function create(
        string $entityType,
        string $linkField = null,
        ReferenceType $referenceType = null
    ) : ?ReferenceType {
        return $this->objectManager->create(
            ReferenceType::class,
            [
                'entityType' => $entityType,
                'linkField' => $linkField,
                'referenceType' => $referenceType
            ]
        );
    }
}
