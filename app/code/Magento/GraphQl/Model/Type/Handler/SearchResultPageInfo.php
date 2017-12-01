<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Type\Definition\Type;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Define SearchResultPageInfo GraphQL type
 */
class SearchResultPageInfo implements HandlerInterface
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
     * @param TypeGenerator $typeGenerator
     * @param TypeFactory $typeFactory
     */
    public function __construct(TypeGenerator $typeGenerator, TypeFactory $typeFactory)
    {
        $this->typeGenerator = $typeGenerator;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return $this->typeFactory->createObject(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Retrieve fields
     *
     * @return Type[]
     */
    private function getFields()
    {
        $reflector = new \ReflectionClass($this);
        $className = $reflector->getShortName();
        $result = [
            'page_size' => 'Int',
            'current_page' => 'Int'
        ];
        $resolvedTypes = $this->typeGenerator->generate($className, $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
