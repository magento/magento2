<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Filter;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\Clause\ReferenceType;

/**
 * Factory for the @see Clause class
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
    ) : Clause {
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
