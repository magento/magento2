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
 * Define Price GraphQL type
 */
class Price implements HandlerInterface
{
    const PRICE_TYPE_NAME = 'Price';

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
                'name' => self::PRICE_TYPE_NAME,
                'fields' => [
                    'amount' => $this->typePool->getType(Money::MONEY_TYPE_NAME),
                    'adjustments' => $this->typeFactory->createList(
                        $this->typePool->getType(PriceAdjustment::PRICE_ADJUSTMENT_TYPE_NAME)
                    ),
                ]
            ]
        );
    }
}
