<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Setup\Model\Declaration\Schema\Dto\Factories\FactoryInterface;

/**
 * This is abstract factory that allows to
 * instantiate any type of structural elements
 *
 * @see ElementInterface
 */
class ElementFactory
{
    /**
     * This is predefined types of elements, that can be instantiate with this factory
     * Where @key - is xsi:type of the object
     * Where @value - is instance class name
     */
    private $typeFactories = [];

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param FactoryInterface[] $typeFactories
     * @param BooleanUtils       $booleanUtils
     */
    public function __construct(array $typeFactories, BooleanUtils $booleanUtils)
    {
        $this->typeFactories = $typeFactories;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * As we have few attributes, that are generic and be applied to few types:
     *  - nullable
     *  - unsigned
     *  - identity
     *
     * We need to cast this attributes to boolean values in abstract factory
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
     * Instantiate different types of elements, depends on their xsi:type
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
