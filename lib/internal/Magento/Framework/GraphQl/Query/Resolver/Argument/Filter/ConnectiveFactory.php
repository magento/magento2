<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Filter;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see Connective class
 */
class ConnectiveFactory
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
     * Create a connective class
     *
     * @param array $conditions
     * @param string|null $operator
     * @return Connective
     */
    public function create(
        array $conditions,
        string $operator = null
    ) : Connective {
        return $this->objectManager->create(
            Connective::class,
            [
                'conditions' => $conditions,
                'operator' => $this->objectManager->create(
                    Operator::class,
                    ['value' => $operator ?: Operator::AND]
                )
            ]
        );
    }
}
