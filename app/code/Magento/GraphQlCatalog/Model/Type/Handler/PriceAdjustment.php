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
 * Define Price adjustment GraphQL type
 */
class PriceAdjustment implements HandlerInterface
{
    const PRICE_ADJUSTMENT_TYPE_NAME = 'PriceAdjustment';

    const ADJUSTMENT_INCLUDED = 'Included';

    const ADJUSTMENT_EXCLUDED = 'Excluded';

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
                'name' => self::PRICE_ADJUSTMENT_TYPE_NAME,
                'fields' => [
                    'amount' => $this->typePool->getType(Money::MONEY_TYPE_NAME),
                    'code' => $this->typePool->getType(PriceAdjustmentCodes::ADJUSTMENTS_TYPE_NAME),
                    'description' => $this->typeFactory->createEnum(
                        [
                            'name' => 'Description',
                            'values' => [self::ADJUSTMENT_INCLUDED, self::ADJUSTMENT_EXCLUDED],
                        ]
                    ),
                ]
            ]
        );
    }
}
