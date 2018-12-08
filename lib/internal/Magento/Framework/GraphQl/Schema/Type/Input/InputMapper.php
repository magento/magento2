<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Input;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Argument;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\TypeFactory;

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
     * Prepare argument's metadata for GraphQL schema generation.
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
            $configElement = $this->config->getConfigElement($typeName);
            $instance = $this->inputFactory->create($configElement);
            $instance = $this->wrappedTypeProcessor->processWrappedType($argument, $instance);
        }

        $calculatedArgument = [
            'type' => $instance,
            'description' => $argument->getDescription()
        ];

        if ($this->scalarTypes->isScalarType($typeName) && $argument->hasDefaultValue()) {
            switch ($argument->getTypeName()) {
                case 'Int':
                    $calculatedArgument['defaultValue'] = (int)$argument->getDefaultValue();
                    break;
                case 'Float':
                    $calculatedArgument['defaultValue'] = (float)$argument->getDefaultValue();
                    break;
                case 'Boolean':
                    $calculatedArgument['defaultValue'] = (bool)$argument->getDefaultValue();
                    break;
                default:
                    $calculatedArgument['defaultValue'] = $argument->getDefaultValue();
            }
        }

        return $calculatedArgument;
    }
}
