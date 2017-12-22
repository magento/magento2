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
 * Define ProductPrice GraphQL type
 */
class ProductPrice implements HandlerInterface
{
    const PRODUCT_PRICE_TYPE_NAME = 'ProductPrice';

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
                'name' => self::PRODUCT_PRICE_TYPE_NAME,
                'fields' => [
                    'minimalPrice' => $this->typePool->getType(Price::PRICE_TYPE_NAME),
                    'regularPrice' => $this->typePool->getType(Price::PRICE_TYPE_NAME),
                    'maximalPrice' => $this->typePool->getType(Price::PRICE_TYPE_NAME)
                ]
            ]
        );
    }
}
