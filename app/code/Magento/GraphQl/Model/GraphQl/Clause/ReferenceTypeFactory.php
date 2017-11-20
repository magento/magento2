<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl\Clause;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ReferenceTypeFactory
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
     * Create ReferenceType
     *
     * @param string $entityType
     * @param string|null $linkField
     * @param ReferenceType|null $referenceType
     * @return ReferenceType
     */
    public function create(string $entityType, string $linkField = null, ReferenceType $referenceType = null)
    {
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
