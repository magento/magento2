<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define Money GraphQL type
 */
class Money implements HandlerInterface
{
    const MONEY_TYPE_NAME = 'Money';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        TypeFactory $typeFactory
    ) {
        $this->typePool = $typePool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->typeFactory->createObject(
            [
                'name' => self::MONEY_TYPE_NAME,
                'fields' => [
                    'value' => $this->typePool->getType(Pool::TYPE_FLOAT),
                    'currency' => $this->typePool->getType(Currency::CURRENCY_TYPE_NAME),
                ]
            ]
        );
    }
}
