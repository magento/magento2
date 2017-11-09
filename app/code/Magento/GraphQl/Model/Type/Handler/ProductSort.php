<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type\Handler;

use GraphQL\Type\Definition\Type;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\GraphQl\Model\Type\Helper\ServiceContract\TypeGenerator as Generator;
use GraphQL\Type\Definition\InputObjectType;
use Magento\GraphQl\Model\Type\HandlerInterface;

/**
 * Define ProductSort's GraphQL type
 */
class ProductSort implements HandlerInterface
{
    /**
     * @var Pool
     */
    private $typePool;

    /**
     * @var Generator
     */
    private $typeGenerator;

    /**
     * @var AttributeManagementInterface
     */
    private $management;

    /**
     * @param Pool $typePool
     * @param Generator $typeGenerator
     * @param AttributeManagementInterface $management
     */
    public function __construct(Pool $typePool, Generator $typeGenerator, AttributeManagementInterface $management)
    {
        $this->typePool = $typePool;
        $this->typeGenerator = $typeGenerator;
        $this->management = $management;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        $reflector = new \ReflectionClass($this);
        return new InputObjectType(
            [
                'name' => $reflector->getShortName(),
                'fields' => $this->getFields(),
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
        $result = [];
        $attributes = $this->management->getAttributes('catalog_product', 4);
        foreach ($attributes as $attribute) {
            if ((!$attribute->getIsUserDefined()) && !is_array($attribute)) {
                $result[$attribute->getAttributeCode()] = 'SortEnum';
            }
        }

        $staticAttributes = $this->typeGenerator->getTypeData('CatalogDataProductInterface');
        foreach ($staticAttributes as $attributeKey => $attribute) {
            if (is_array($attribute)) {
                unset($staticAttributes[$attributeKey]);
            } else {
                $staticAttributes[$attributeKey] = 'SortEnum';
            }
        }

        $result = array_merge($result, $staticAttributes);

        $reflector = new \ReflectionClass($this);
        $resolvedTypes = $this->typeGenerator->generate($reflector->getShortName(), $result);
        $fields = $resolvedTypes->config['fields'];

        return $fields;
    }
}
