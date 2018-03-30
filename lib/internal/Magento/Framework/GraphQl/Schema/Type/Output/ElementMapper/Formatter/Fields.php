<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\Formatter;

use Magento\Framework\GraphQl\Config\Data\WrappedTypeProcessor;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\TypeInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\Input\InputMapper;
use Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\Schema\Type\OutputType;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
use Magento\Framework\GraphQl\Schema\TypeFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Convert fields of the given 'type' config element to the objects compatible with GraphQL schema generator.
 */
class Fields implements FormatterInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @param OutputMapper $outputMapper
     * @param InputMapper $inputMapper
     * @param TypeFactory $typeFactory
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        OutputMapper $outputMapper,
        InputMapper $inputMapper,
        TypeFactory $typeFactory,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor
    ) {
        $this->objectManager = $objectManager;
        $this->outputMapper = $outputMapper;
        $this->inputMapper = $inputMapper;
        $this->typeFactory = $typeFactory;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function format(TypeInterface $configElement, OutputType $outputType): array
    {
        $typeConfig = [
            'fields' => function () use ($configElement, $outputType) {
                $fieldsConfig = [];
                foreach ($configElement->getFields() as $field) {
                    $fieldsConfig[$field->getName()] = $this->getFieldConfig($configElement, $outputType, $field);
                }
                return $fieldsConfig;
            }
        ];
        return $typeConfig;
    }

    /**
     * Get field's type object compatible with GraphQL schema generator.
     *
     * @param TypeInterface $typeConfigElement
     * @param OutputType $outputType
     * @param Field $field
     * @return TypeInterface
     */
    private function getFieldType(TypeInterface $typeConfigElement, OutputType $outputType, Field $field)
    {
        if ($this->scalarTypes->isScalarType($field->getTypeName())) {
            $type = $this->wrappedTypeProcessor->processScalarWrappedType($field);
        } else {
            if ($typeConfigElement->getName() == $field->getTypeName()) {
                $type = $outputType;
            } else {
                $type = $this->outputMapper->getOutputType($field->getTypeName());
            }

            $type = $this->wrappedTypeProcessor->processWrappedType($field, $type);
        }
        return $type;
    }

    /**
     * Generate field config.
     *
     * @param TypeInterface $typeConfigElement
     * @param OutputType $outputType
     * @param Field $field
     * @return array
     */
    private function getFieldConfig(TypeInterface $typeConfigElement, OutputType $outputType, Field $field): array
    {
        $type = $this->getFieldType($typeConfigElement, $outputType, $field);
        $fieldConfig = [
            'name' => $field->getName(),
            'type' => $type,
        ];

        if (!empty($field->getDescription())) {
            $fieldConfig['description'] = $field->getDescription();
        }

        if ($field->getResolver() != null) {
            /** @var ResolverInterface $resolver */
            $resolver = $this->objectManager->get($field->getResolver());

            $fieldConfig['resolve'] =
                function ($value, $args, $context, $info) use ($resolver, $field) {
                    return $resolver->resolve($field, $value, $args, $context, $info);
                };
        }
        return $this->formatArguments($field, $fieldConfig);
    }

    /**
     * Format arguments configured for passed in field.
     *
     * @param Field $field
     * @param array $config
     * @return array
     */
    private function formatArguments(Field $field, array $config) : array
    {
        foreach ($field->getArguments() as $argument) {
            $inputType = $this->inputMapper->getRepresentation($argument);

            $config['args'][$argument->getName()] = $inputType;
        }

        return $config;
    }
}
