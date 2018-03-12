<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Type\Definition\InputType;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Config\Data\Type as TypeStructure;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Type\Definition\NonNull;

/**
 * Class InputObjectType
 */
class InputObjectType extends \Magento\Framework\GraphQl\Type\Definition\InputObjectType
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(
        InputMapper $inputMapper,
        TypeStructure $structure,
        TypeFactory $typeFactory
    ) {
        $this->typeFactory = $typeFactory;
        $config = [
            'name' => $structure->getName(),
            'description' => $structure->getDescription()
        ];
        foreach ($structure->getFields() as $field) {
            if ($field->getType() == $structure->getName()) {
                $type = $this;
            } elseif ($field->isList()) {
                $type = $inputMapper->getFieldRepresentation($field->getItemType());
            } else {
                $type = $inputMapper->getFieldRepresentation($field->getType());
            }

            $type = $this->processIsNullable($field, $this->processIsList($field, $type));

            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type
            ];
        }
        parent::__construct($config);
    }

    /**
     * Return passed in type wrapped as a non null type if definition determines necessary.
     *
     * @param Field $field
     * @param InputType $object
     * @return TypeInterface|InputType
     */
    private function processIsNullable(Field $field, InputType $object) : TypeInterface
    {
        if ($field->isRequired()) {
            return $this->typeFactory->createNonNull($object);
        }
        return $object;
    }

    /**
     * Return passed in type wrapped as a list if definition determines necessary.
     *
     * @param Field $field
     * @param InputType $object
     * @return TypeInterface|InputType
     */
    private function processIsList(Field $field, InputType $object) : TypeInterface
    {
        if ($field->isList()) {
            return $this->typeFactory->createList($object);
        }
        return $object;
    }
}
