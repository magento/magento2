<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Filter;

use Magento\Framework\ObjectManagerInterface;

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
     * @param string $fieldName
     * @param string $clauseType
     * @param string|array $clauseValue
     * @return Clause
     */
    public function create(
        string $fieldName,
        string $clauseType,
        $clauseValue
    ) : Clause {
        return $this->objectManager->create(
            Clause::class,
            [
                'fieldName' => $fieldName,
                'clauseType' => $clauseType,
                'clauseValue' => $clauseValue
            ]
        );
    }
}
