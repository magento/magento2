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
use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Query\Resolver\PromiseFactory;
use Magento\Framework\GraphQl\Schema\Type\Input\InputMapper;
use Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\FormatterInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\GraphQl\Schema\Type\ScalarTypes;
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
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var WrappedTypeProcessor
     */
    private $wrappedTypeProcessor;

    /**
     * @var PromiseFactory
     */
    private $promiseFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param OutputMapper $outputMapper
     * @param InputMapper $inputMapper
     * @param ScalarTypes $scalarTypes
     * @param WrappedTypeProcessor $wrappedTypeProcessor
     * @param mixed $resolveInfoFactory
     * @param mixed $resolverFactory
     * @param PromiseFactory|null $promiseFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        OutputMapper $outputMapper,
        InputMapper $inputMapper,
        ScalarTypes $scalarTypes,
        WrappedTypeProcessor $wrappedTypeProcessor,
        $resolveInfoFactory = null,
        $resolverFactory = null,
        ?PromiseFactory $promiseFactory = null
    ) {
        $this->objectManager = $objectManager;
        $this->outputMapper = $outputMapper;
        $this->inputMapper = $inputMapper;
        $this->scalarTypes = $scalarTypes;
        $this->wrappedTypeProcessor = $wrappedTypeProcessor;
        $this->promiseFactory = $promiseFactory ?? $this->objectManager->get(PromiseFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function format(ConfigElementInterface $configElement, OutputTypeInterface $outputType): array
    {
        $typeConfig = [];
        if ($configElement instanceof TypeInterface) {
            $typeConfig = [
                'fields' => function () use ($configElement, $outputType) {
                    $fieldsConfig = [];
                    foreach ($configElement->getFields() as $field) {
                        $fieldsConfig[$field->getName()] = $this->getFieldConfig($configElement, $outputType, $field);
                    }
                    return $fieldsConfig;
                }
            ];
        }
        return $typeConfig;
    }

    /**
     * Get field's type object compatible with GraphQL schema generator.
     *
     * @param TypeInterface $typeConfigElement
     * @param OutputTypeInterface $outputType
     * @param Field $field
     * @return TypeInterface
     */
    private function getFieldType(TypeInterface $typeConfigElement, OutputTypeInterface $outputType, Field $field)
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
     * @param OutputTypeInterface $outputType
     * @param Field $field
     * @return array
     */
    private function getFieldConfig(
        TypeInterface $typeConfigElement,
        OutputTypeInterface $outputType,
        Field $field
    ): array {
        $type = $this->getFieldType($typeConfigElement, $outputType, $field);
        $fieldConfig = [
            'name' => $field->getName(),
            'type' => $type,
        ];

        if (!empty($field->getDescription())) {
            $fieldConfig['description'] = $field->getDescription();
        }

        if (!empty($field->getDeprecated())) {
            if (isset($field->getDeprecated()['reason'])) {
                $fieldConfig['deprecationReason'] = $field->getDeprecated()['reason'];
            }
        }

        if ($field->getResolver() != null) {
            $fieldConfig['resolve'] = $this->promiseFactory->create($field);
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
