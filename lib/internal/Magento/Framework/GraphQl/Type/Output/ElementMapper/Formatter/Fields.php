<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper\Formatter;

use Magento\Framework\GraphQl\Type\Definition\OutputType;
use Magento\Framework\GraphQl\ArgumentFactory;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Type\Input\InputMapper;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Config\FieldConfig;
use Magento\Framework\GraphQl\Type\Definition\ScalarTypes;
use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;

/**
 * Formats all fields configured for given type structure, if any.
 */
class Fields implements FormatterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FieldConfig
     */
    private $fieldConfig;

    /**
     * @var ArgumentFactory
     */
    private $argumentFactory;

    /**
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * @var InputMapper
     */
    private $inputMapper;

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
     * @param ObjectManagerInterface $objectManager
     * @param FieldConfig $fieldConfig
     * @param ArgumentFactory $argumentFactory
     * @param OutputMapper $outputMapper
     * @param InputMapper $inputMapper
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FieldConfig $fieldConfig,
        ArgumentFactory $argumentFactory,
        OutputMapper $outputMapper,
        InputMapper $inputMapper,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor
    ) {
        $this->objectManager = $objectManager;
        $this->fieldConfig = $fieldConfig;
        $this->argumentFactory = $argumentFactory;
        $this->outputMapper = $outputMapper;
        $this->inputMapper = $inputMapper;
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType) : array
    {
        $config = [];
        /** @var Field $field */
        foreach ($typeStructure->getFields() as $field) {
            if ($this->scalarTypes->hasScalarTypeClass($field->getType())) {
                $type = $this->wrappedTypeProcessor->processScalarWrappedType($field);
            } else {
                if ($typeStructure->getName() == $field->getType()) {
                    $type = $outputType;
                } else {
                    if ($typeStructure->getName() == $field->getType()) {
                        $type = $outputType;
                    } else {
                        $type = $this->outputMapper->getTypeObject($field->getType());
                    }

                    $type = $this->wrappedTypeProcessor->processWrappedType($field, $type);
                }
            }
            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type,
            ];

            if (!empty($field->getDescription())) {
                $config['fields'][$field->getName()]['description'] = $field->getDescription();
            }

            if ($field->getResolver() != null) {
                /** @var ResolverInterface $resolver */
                $resolver = $this->objectManager->get($field->getResolver());

                $config['fields'][$field->getName()]['resolve'] =
                    function ($value, $args, $context, $info) use ($resolver, $field) {
                        return $resolver->resolve($field, $value, $args, $context, $info);
                    };
            }
            $config = $this->formatArguments($field, $config);
        }
        return $config;
    }

    /**
     * Format arguments configured for passed in field.
     *
     * @param Field $field
     * @param array $config
     * @return array
     */
    private function formatArguments(Field $field, array $config)
    {
        foreach ($field->getArguments() as $argument) {
            $inputType = $this->inputMapper->getRepresentation($argument);

            $config['fields'][$field->getName()]['args'][$argument->getName()] = $inputType;
        }

        return $config;
    }
}
