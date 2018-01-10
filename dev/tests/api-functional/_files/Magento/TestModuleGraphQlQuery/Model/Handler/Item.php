<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleGraphQlQuery\Model\Handler;

use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\GraphQl\Model\Type\Handler\Pool;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\GraphQl\Model\Type\ServiceContract\TypeGenerator;

class Item implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @param TypeGenerator $typeGenerator
     * @param TypeFactory $typeFactory
     * @param Pool $pool
     */
    public function __construct(TypeGenerator $typeGenerator, TypeFactory $typeFactory, Pool $pool)
    {
        $this->typeGenerator = $typeGenerator;
        $this->typeFactory = $typeFactory;
        $this->pool = $pool;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return  $this->typeFactory->createObject(
            [
                'name' => 'Item',
                'fields' => $this->getFields()
            ]
        );
    }

    private function getFields()
    {
        $fields = $this->typeGenerator->getTypeData('TestModuleGraphQlQueryDataItemInterface');
        $resolvedTypes = $this->typeGenerator->generate('Item', $fields);
        $result = $resolvedTypes->config['fields'];
        return $result;
    }
}
