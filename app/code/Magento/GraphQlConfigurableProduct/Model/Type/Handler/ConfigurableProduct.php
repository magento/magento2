<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlConfigurableProduct\Model\Type\Handler;

use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;

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
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param Pool $typePool
     * @param TypeFactory $typeFactory
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
        $interface = $this->typePool->getType('Product');
        $fields = array_merge($fields, $interface->config['fields']);
        $fields['configurable_product_links'] =  $this->typeFactory->createList(
            $this->typePool->getComplexType('Product')
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
