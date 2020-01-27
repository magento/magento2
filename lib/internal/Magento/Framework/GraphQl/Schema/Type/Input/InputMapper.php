<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Input;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Argument;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;

/**
 * Prepare argument's metadata for GraphQL schema generation
 */
class InputMapper
{
    /**
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor,
        TypeRegistry $typeRegistry
    ) {
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Prepare argument's metadata for GraphQL schema generation.
     *
     * @param Argument $argument
     * @return array
     * @throws GraphQlInputException
     */
    public function getRepresentation(Argument $argument) : array
    {
        $typeName = $argument->getTypeName();
        if ($this->scalarTypes->isScalarType($typeName)) {
            $instance = $this->wrappedTypeProcessor->processScalarWrappedType($argument);
        } else {
            $instance = $this->typeRegistry->get($typeName);
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
