<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Find;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Argument\Find\Clause\ReferenceType;

/**
 * Factory for Clause Class
 */
class ClauseFactory
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
     * Create a clause class
     *
     * @param ReferenceType $referenceType
     * @param string $fieldName
     * @param string $clauseType
     * @param string|array $clauseValue
     * @return Clause
     */
    public function create(
        ReferenceType $referenceType,
        string $fieldName,
        string $clauseType,
        $clauseValue
    ) {
        return $this->objectManager->create(
            Clause::class,
            [
                'referenceType' => $referenceType,
                'fieldName' => $fieldName,
                'clauseType' => $clauseType,
                'clauseValue' => $clauseValue
            ]
        );
    }
}
