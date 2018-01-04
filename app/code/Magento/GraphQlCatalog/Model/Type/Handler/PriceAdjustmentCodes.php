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
 * Define PriceAdjustmentCodes GraphQL type
 */
class PriceAdjustmentCodes implements HandlerInterface
{
    const ADJUSTMENTS_TYPE_NAME = 'PriceAdjustmentCodes';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\Pool
     */
    private $typeGenerator;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param \Magento\Framework\Pricing\Adjustment\Pool $typeGenerator
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        Pool $typePool,
        \Magento\Framework\Pricing\Adjustment\Pool $typeGenerator,
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
        return $this->typeFactory->createEnum(
            [
                'name' => self::ADJUSTMENTS_TYPE_NAME,
                'values' => array_keys($this->typeGenerator->getAdjustments()),
            ]
        );
    }
}
