<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use \GraphQL\Type\Definition\EnumType;
use Magento\GraphQl\Model\Type\Generator;
use Magento\GraphQl\Model\Type\HandlerInterface;

/**
 * Define SortEnum's GraphQL Type
 */
class SortEnum implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var Generator
     */
    private $typeGenerator;

    /**
     * @param Pool $typePool
     * @param Generator $typeGenerator
     */
    public function __construct(Pool $typePool, Generator $typeGenerator)
    {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return new EnumType(
            [
                'name' => $reflector->getShortName(),
                'values' => ['ASC', 'DESC'],
            ]
        );
    }
}
