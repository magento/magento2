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
use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;

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
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @param InputFactory $inputFactory
     * @param ConfigInterface $config
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     */
    public function __construct(
        InputFactory $inputFactory,
        ConfigInterface $config,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor
    ) {
        $this->inputFactory = $inputFactory;
        $this->config = $config;
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
    }

    /**
     * Determine an arguments type and structure for schema generation.
     *
     * @param Argument $argument
     * @return array
     */
    public function getRepresentation(Argument $argument) : array
    {
        $typeName = $argument->getTypeName();
        if ($this->scalarTypes->isScalarType($typeName)) {
            $instance = $this->wrappedTypeProcessor->processScalarWrappedType($argument);
        } else {
            $configElement = $this->config->getTypeStructure($typeName);
            $instance = $this->inputFactory->create($configElement);
            $instance = $this->wrappedTypeProcessor->processWrappedType($argument, $instance);
        }

        $calculatedArgument = [
            'type' => $instance,
            'description' => $argument->getDescription()
        ];

        if ($this->scalarTypes->isScalarType($typeName) && $argument->getDefault() !== null) {
            switch ($argument->getTypeName()) {
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
}
