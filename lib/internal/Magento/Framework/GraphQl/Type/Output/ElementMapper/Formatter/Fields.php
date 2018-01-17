<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper\Formatter;

use GraphQL\Type\Definition\OutputType;
use Magento\Framework\GraphQl\ArgumentFactory;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;
use Magento\Framework\GraphQl\Type\Input\InputMapper;
use Magento\Framework\GraphQl\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Type\Definition\TypeInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Config\FieldConfig;

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
     * @param ObjectManagerInterface $objectManager
     * @param FieldConfig $fieldConfig
     * @param ArgumentFactory $argumentFactory
     * @param OutputMapper $outputMapper
     * @param InputMapper $inputMapper
     * @param TypeFactory $typeFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FieldConfig $fieldConfig,
        ArgumentFactory $argumentFactory,
        OutputMapper $outputMapper,
        InputMapper $inputMapper,
        TypeFactory $typeFactory
    ) {
        $this->objectManager = $objectManager;
        $this->fieldConfig = $fieldConfig;
        $this->argumentFactory = $argumentFactory;
        $this->outputMapper = $outputMapper;
        $this->inputMapper = $inputMapper;
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function format(StructureInterface $typeStructure, OutputType $outputType)
    {
        $config = [];
        foreach ($typeStructure->getFields() as $field) {
            $type = $this->getFieldType($typeStructure, $field, $outputType);
            $config['fields'][$field->getName()] = [
                'name' => $field->getName(),
                'type' => $type,
            ];
            if ($field->getResolver() != null) {
                /** @var \Magento\GraphQl\Model\ResolverInterface $resolver */
                $resolver = $this->objectManager->get($field->getResolver());

                $config['fields'][$field->getName()]['resolve'] =
                    function ($value, $args, $context, $info) use ($resolver, $field) {
                        $infoData = [];
                        foreach ($info->fieldNodes as $item) {
                            $infoData[] = $item->toArray(true);
                        }
                        $this->clearInfo($infoData);

                        $fieldArguments = [];
                        $declaredArguments = $this->fieldConfig->getFieldConfig($field->getName(), $args);

                        foreach ($declaredArguments as $argumentName => $declaredArgument) {
                            $argumentValue = isset($args[$argumentName])
                                ? $args[$argumentName]
                                : $declaredArgument->getDefaultValue();
                            if ($declaredArgument->getValueParser() && $argumentValue !== null) {
                                $argumentValue = $declaredArgument->getValueParser()->parse($argumentValue);
                            }

                            if ($argumentValue !== null) {
                                $fieldArguments[$argumentName] = $this->argumentFactory->create(
                                    $argumentName,
                                    $argumentValue
                                );
                            }
                        }

                        return $resolver->resolve($fieldArguments, $context);
                    };
            }
            $config = $this->formatArguments($field, $config);
        }
        return $config;
    }

    /**
     * Return passed in type wrapped as a non null type if definition determines necessary.
     *
     * @param Field $field
     * @param OutputType $object
     * @return TypeInterface|\GraphQL\Type\Definition\Type
     */
    private function processIsNullable(Field $field, OutputType $object)
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
     * @param OutputType $object
     * @return TypeInterface|\GraphQL\Type\Definition\Type
     */
    private function processIsList(Field $field, OutputType $object)
    {
        if ($field->isList()) {
            return $this->typeFactory->createList($object);
        }
        return $object;
    }

    /**
     * Determine field's type based on configured attributes.
     *
     * @param StructureInterface $typeStructure
     * @param Field $field
     * @param OutputType $outputType
     * @return OutputType
     */
    private function getFieldType(StructureInterface $typeStructure, Field $field, OutputType $outputType)
    {
        if ($typeStructure->getName() == $field->getType()) {
            $type = $outputType;
        } elseif ($field->isList()) {
            $type = $this->outputMapper->getTypeObject($field->getItemType());
        } else {
            $type = $this->outputMapper->getTypeObject($field->getType());
        }

        $type = $this->processIsNullable($field, $this->processIsList($field, $type));

        return $type;
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

    /**
     * Clear superfluous information from request array.
     *
     * @param array $data
     * @return void
     */
    private function clearInfo(array &$data)
    {
        unset($data['loc']);
        foreach ($data as &$value) {
            if (is_array($value)) {
                $this->clearInfo($value);
            }
        }
    }
}
