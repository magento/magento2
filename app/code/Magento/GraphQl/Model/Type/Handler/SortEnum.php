<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\GraphQl\Model\Type\Generator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define SortEnum GraphQL Type
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
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param Generator $typeGenerator
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        Generator $typeGenerator,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createEnum(
            [
                'name' => $reflector->getShortName(),
                'values' => ['ASC', 'DESC'],
            ]
        );
    }
}
