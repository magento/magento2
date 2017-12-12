<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlConfigurableProduct\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQlCatalog\Model\Type\Handler\Product;
use Magento\GraphQlCatalog\Model\Type\Handler\SimpleProduct;

/**
 * Define ConfigurableProduct's GraphQL type
 */
class ConfigurableProduct implements HandlerInterface
{
    const CONFIGURABLE_PRODUCT_TYPE_NAME = 'ConfigurableProduct';

    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var \Magento\Framework\GraphQl\TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param \Magento\Framework\GraphQl\TypeFactory $typeFactory
     */
    public function __construct(Pool $typePool, TypeFactory $typeFactory)
    {
        $this->typePool = $typePool;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $fields = [];
        $interface = $this->typePool->getType(Product::PRODUCT_TYPE_NAME);
        $fields = array_merge($fields, $interface->config['fields']);
        $fields['configurable_product_links'] =  $this->typeFactory->createList(
            $this->typePool->getType(SimpleProduct::SIMPLE_PRODUCT_TYPE_NAME)
        );

        return $this->typeFactory->createObject(
            [
                'name' => self::CONFIGURABLE_PRODUCT_TYPE_NAME,
                'fields' => $fields,
                'interfaces' => [$interface]
            ]
        );
    }
}
