<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Type\Handler;

use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator as Generator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

/**
 * Define Price adjustment GraphQL type
 */
class PriceAdjustment implements HandlerInterface
{
    const PRICE_ADJUSTMENT_TYPE_NAME = 'PriceAdjustment';

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
        return $this->typeFactory->createObject(
            [
                'name' => self::PRICE_ADJUSTMENT_TYPE_NAME,
                'fields' => [
                    'amount' => $this->typePool->getType(Money::MONEY_TYPE_NAME),
                    'code' => $this->typeFactory->createList(
                        $this->typePool->getType(PriceAdjustmentCodes::ADJUSTMENTS_TYPE_NAME)
                    ),
                    'description' => $this->typeFactory->createList($this->typePool->getType(Pool::TYPE_STRING)),
                ]
            ]
        );
    }
}
