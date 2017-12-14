<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This is abstract factory that allows to
 * instantiate any type of structural elements
 * @see ElementInterface
 *
 */
class ElementFactory
{
    /**
     * This is predefined types of elements, that can be instantiate with this factory
     * Where @key - is xsi:type of the object
     * Where @value - is instance class name
     */
    private $types = [];

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * Instantiate different types of elements, depends on their xsi:type
     *
     * @param string $type
     * @param array $elementStructuralData
     * @return ElementInterface | object
     */
    public function create($type, array $elementStructuralData)
    {
        if (!isset($this->types[$type])) {
            throw new \InvalidArgumentException(sprintf("Types %s is not declared", $type));
        }

        $elementStructuralData['type'] = $type;
        return new $this->types[$type]($elementStructuralData, $type);
    }
}
