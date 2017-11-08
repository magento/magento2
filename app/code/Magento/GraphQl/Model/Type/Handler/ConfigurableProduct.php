<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;

/**
 * Define ConfigurableProduct's GraphQL type
 */
class ConfigurableProduct implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @param Pool $typePool
     */
    public function __construct(Pool $typePool)
    {
        $this->typePool = $typePool;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        $fields = [];
        $interface = $this->typePool->getType('Product');
        $fields = array_merge($fields, $interface->config['fields']);
        $fields['configurable_product_links'] = new ListOfType($this->typePool->getComplexType('SimpleProduct'));
        return new ObjectType(
            [
                'name' => $reflector->getShortName(),
                'fields' => $fields,
                'interfaces' => [$interface]
            ]
        );
    }
}
