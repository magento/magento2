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
use Magento\Framework\GraphQl\Config\ConfigInterface;

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
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var ConfigInterface
     */
    public $graphQlConfig;

    /**
     * @param TypeStructure $structure
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     * @param InputFactory $inputFactory
     * @param ConfigInterface $graphQlConfig
     */
    public function __construct(
        TypeStructure $structure,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor,
        InputFactory $inputFactory,
        ConfigInterface $graphQlConfig
    ) {
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $this->inputFactory = $inputFactory;
        $this->graphQlConfig = $graphQlConfig;
        $config = [
            'name' => $structure->getName(),
            'description' => $structure->getDescription()
        ];
        foreach ($structure->getFields() as $field) {
            if ($this->scalarTypes->isScalarType($field->getTypeName())) {
                $type = $type = $this->wrappedTypeProcessor->processScalarWrappedType($field);
            } else {
                if ($field->getTypeName() == $structure->getName()) {
                    $type = $this;
                } else {
                    $configElement = $this->graphQlConfig->getTypeStructure($field->getTypeName());
                    $type = $this->inputFactory->create($configElement);
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
