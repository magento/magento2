<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Config\Data\Argument;
use Magento\Framework\GraphQl\Type\Definition\InputType;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Type\Definition\ScalarTypes;

/**
 * Class OutputMapper
 */
class InputMapper
{
    /**
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @param InputFactory $inputFactory
     * @param ConfigInterface $config
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     */
    public function __construct(
        InputFactory $inputFactory,
        ConfigInterface $config,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes
    ) {
        $this->inputFactory = $inputFactory;
        $this->config = $config;
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
    }

    /**
     * Determine an arguments type and structure for schema generation.
     *
     * @param Argument $argument
     * @return array
     */
    public function getRepresentation(Argument $argument) : array
    {
        $type = $argument->getType();
        $calculateDefault = true;
        if ($this->scalarTypes->hasScalarTypeClass($type)) {
            $instance = $this->scalarTypes->getScalarTypeInstance($type);
            if ($argument->isList()) {
                $instance = $argument->areItemsRequired() ? $this->scalarTypes->createNonNull($instance) : $instance;
                $instance = $this->scalarTypes->createList($instance);
            }
            if ($argument->isRequired()) {
                $instance = $this->scalarTypes->createNonNull($instance);
            }
        } else {
            $configElement = $this->config->getTypeStructure($type);
            $instance = $this->inputFactory->create($configElement);
            $calculateDefault = false;
            if ($argument->isList()) {
                $instance = $argument->areItemsRequired() ? $this->typeFactory->createNonNull($instance) : $instance;
                $instance = $this->typeFactory->createList($instance);
            }
            if ($argument->isRequired()) {
                $instance = $this->typeFactory->createNonNull($instance);
            }
        }

        $calculatedArgument = [
            'type' => $instance,
            'description' => $argument->getDescription()
        ];

        if ($calculateDefault && $argument->getDefault() !== null) {
            switch ($argument->getType()) {
                case 'Int':
                    $calculatedArgument['defaultValue'] = (int)$argument->getDefault();
                    break;
                case 'Float':
                    $calculatedArgument['defaultValue'] = (float)$argument->getDefault();
                    break;
                case 'Boolean':
                    $calculatedArgument['defaultValue'] = (bool)$argument->getDefault();
                    break;
                default:
                    $calculatedArgument['defaultValue'] = $argument->getDefault();
            }
        }

        return $calculatedArgument;
    }

    /**
     * Return object representation of field for passed in type.
     *
     * @param string $type
     * @return InputType
     */
    public function getFieldRepresentation(string $type) : InputType
    {
        if ($this->scalarTypes->hasScalarTypeClass($type)) {
            return $this->scalarTypes->getScalarTypeInstance($type);
        } else {
            $configElement = $this->config->getTypeStructure($type);
            return $this->inputFactory->create($configElement);
        }
    }
}
