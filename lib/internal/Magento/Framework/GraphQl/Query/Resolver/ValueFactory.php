<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create @see Value to return data from passed in callback to GraphQL library
 */
class ValueFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create value with passed in callback that returns data as parameter;
     *
     * @param callable $callback
     * @return Value
     */
    public function create(callable $callback)
    {
        return $this->objectManager->create(Value::class, ['executor' => $callback]);
    }
}
