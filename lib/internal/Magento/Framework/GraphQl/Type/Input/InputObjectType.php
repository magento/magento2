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
use Magento\Framework\GraphQl\Type\Definition\ScalarTypes;
use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;

/**
 * Class InputObjectType
 */
class InputObjectType extends \Magento\Framework\GraphQl\Type\Definition\InputObjectType
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @param InputMapper $inputMapper
     * @param TypeStructure $structure
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     */
    public function __construct(
        InputMapper $inputMapper,
        TypeStructure $structure,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor
    ) {
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $config = [
            'name' => $structure->getName(),
            'description' => $structure->getDescription()
        ];
        foreach ($structure->getFields() as $field) {
            if ($this->scalarTypes->hasScalarTypeClass($field->getType())) {
                $type = $type = $this->wrappedTypeProcessor->processScalarWrappedType($field);
            } else {
                if ($field->getType() == $structure->getName()) {
                    $type = $this;
                } else {
                    $type = $inputMapper->getFieldRepresentation($field->getType());
                }
                $type = $this->wrappedTypeProcessor->processWrappedType($field, $type);
            }

            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type
            ];
        }
        parent::__construct($config);
    }
}
