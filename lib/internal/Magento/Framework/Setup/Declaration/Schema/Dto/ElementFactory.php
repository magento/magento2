<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Setup\Declaration\Schema\Dto\Factories\FactoryInterface;

/**
 * DTO Element factory.
 *
 * Instantiates any type of structural elements.
 *
 * @see ElementInterface
 */
class ElementFactory
{
    /**
     * Predefined types of elements, that can be instantiated using this factory.
     *
     * Where @key - is xsi:type of the object
     * Where @value - is instance class name
     */
    private $typeFactories = [];

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Constructor.
     *
     * @param FactoryInterface[] $typeFactories
     * @param BooleanUtils       $booleanUtils
     */
    public function __construct(
        array $typeFactories,
        BooleanUtils $booleanUtils
    ) {
        $this->typeFactories = $typeFactories;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Cast generic attributes.
     *
     * Common attributes for multiple types:
     *  - nullable
     *  - unsigned
     *  - identity
     * Casted to boolean value in factory.
     *
     * @param  array $elementStructuralData
     * @return array
     */
    private function castGenericAttributes(array $elementStructuralData)
    {
        $booleanAttributes = ['nullable', 'unsigned', 'identity'];

        foreach ($booleanAttributes as $booleanAttribute) {
            if (isset($elementStructuralData[$booleanAttribute])) {
                $elementStructuralData[$booleanAttribute] = $this->booleanUtils
                    ->toBoolean($elementStructuralData[$booleanAttribute]);
            }
        }

        return $elementStructuralData;
    }

    /**
     * Instantiate different types of elements, depends on their xsi:type.
     *
     * @param  string $type
     * @param  array  $elementStructuralData
     * @return ElementInterface | object
     */
    public function create($type, array $elementStructuralData)
    {
        if (!isset($this->typeFactories[$type])) {
            throw new \InvalidArgumentException(sprintf("Types %s is not declared", $type));
        }

        $elementStructuralData = $this->castGenericAttributes($elementStructuralData);
        $elementStructuralData['type'] = $type;
        return $this->typeFactories[$type]->create($elementStructuralData);
    }
}
