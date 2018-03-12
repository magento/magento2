<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Input;

use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Config\Data\Argument;
use Magento\Framework\GraphQl\Type\Definition\InputType;
use Magento\Framework\GraphQl\TypeFactory;

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
     * @param InputFactory $inputFactory
     * @param ConfigInterface $config
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        InputFactory $inputFactory,
        ConfigInterface $config,
        TypeFactory $typeFactory
    ) {
        $this->inputFactory = $inputFactory;
        $this->config = $config;
        $this->typeFactory = $typeFactory;
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
        $instance = $this->typeFactory->getScalar($type);
        $calculateDefault = true;
        if (!$instance) {
            $configElement = $this->config->getTypeStructure($type);
            $instance = $this->inputFactory->create($configElement);
            $calculateDefault = false;
        }

        if ($argument->isList()) {
            $instance = $argument->areItemsRequired() ? $this->typeFactory->createNonNull($instance) : $instance;
            $instance = $this->typeFactory->createList($instance);
        }
        $calculatedArgument = [
            'type' => $argument->isRequired() ? $this->typeFactory->createNonNull($instance) : $instance,
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
        $instance = $this->typeFactory->getScalar($type);
        if (!$instance) {
            $configElement = $this->config->getTypeStructure($type);
            $instance = $this->inputFactory->create($configElement);
        }
        return $instance;
    }
}
