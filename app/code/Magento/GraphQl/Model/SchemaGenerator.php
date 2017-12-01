<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use GraphQl\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Type\SchemaFactory;
use Magento\Framework\GraphQl\Type\Schema;
use Magento\GraphQl\Model\Type\Generator;
use Magento\Framework\GraphQl\ArgumentFactory;
use Magento\Framework\GraphQl\Type\TypeFactory;

/**
 * Generate a query field and concrete types for GraphQL schema
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var Generator
     */
    private $typeGenerator;

    /**
     * @var ResolverFactory
     */
    private $resolverFactory;

    /**
     * @var ArgumentFactory
     */
    private $argumentFactory;

    /**
     * @var FieldConfig
     */
    private $fieldConfig;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @param Generator $typeGenerator
     * @param ResolverFactory $resolverFactory
     * @param ArgumentFactory $argumentFactory
     * @param FieldConfig $fieldConfig
     * @param TypeFactory $typeFactory
     * @param SchemaFactory $schemaFactory
     */
    public function __construct(
        Generator $typeGenerator,
        ResolverFactory $resolverFactory,
        ArgumentFactory $argumentFactory,
        FieldConfig $fieldConfig,
        TypeFactory $typeFactory,
        SchemaFactory $schemaFactory
    ) {
        $this->typeGenerator = $typeGenerator;
        $this->resolverFactory = $resolverFactory;
        $this->argumentFactory = $argumentFactory;
        $this->fieldConfig = $fieldConfig;
        $this->typeFactory = $typeFactory;
        $this->schemaFactory = $schemaFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generate()
    {
        $schemaConfig = $this->typeGenerator->generateTypes('Query');

        $config = $this->typeFactory->createObject([
            'name' => 'Query',
            'fields' => $schemaConfig['fields'],
            'resolveField' => function ($value, $args, $context, ResolveInfo $info) {
                $fieldName = $info->fieldName;
                $resolver = $this->resolverFactory->create($fieldName);

                $fieldArguments = [];
                $declaredArguments = $this->fieldConfig->getFieldConfig($fieldName, $args);

                foreach ($declaredArguments as $argumentName => $declaredArgument) {
                    $argumentValue = isset($args[$argumentName])
                        ? $args[$argumentName]
                        : $declaredArgument->getDefaultValue();
                    if ($declaredArgument->getValueParser()) {
                        $argumentValue = $declaredArgument->getValueParser()->parse($argumentValue);
                    }
                    $fieldArguments[$argumentName] = $this->argumentFactory->create(
                        $argumentName,
                        $argumentValue
                    );
                }

                return $resolver->resolve($fieldArguments);
            }
        ]);
        $schema = $this->schemaFactory->create(['query' => $config, 'types' => $schemaConfig['types']]);
        return $schema;
    }
}
