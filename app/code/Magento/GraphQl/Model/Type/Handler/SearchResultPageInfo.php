<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use \GraphQL\Type\Definition\ObjectType;
use \GraphQL\Type\Definition\Type;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator;
use Magento\GraphQl\Model\Type\HandlerInterface;

/**
 * Define SearchResultPageInfo's GraphQL type
 */
class SearchResultPageInfo implements HandlerInterface
{
    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @param TypeGenerator $typeGenerator
     */
    public function __construct(TypeGenerator $typeGenerator)
    {
        $this->typeGenerator = $typeGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return new ObjectType(
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
