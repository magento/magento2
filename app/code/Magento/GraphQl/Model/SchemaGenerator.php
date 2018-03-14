<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model;

use Magento\Framework\GraphQl\Type\SchemaFactory;
use Magento\GraphQl\Model\Type\Generator;
use Magento\Framework\GraphQl\TypeFactory;

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
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @param Generator $typeGenerator
     * @param TypeFactory $typeFactory
     * @param SchemaFactory $schemaFactory
     */
    public function __construct(
        Generator $typeGenerator,
        TypeFactory $typeFactory,
        SchemaFactory $schemaFactory
    ) {
        $this->typeGenerator = $typeGenerator;
        $this->typeFactory = $typeFactory;
        $this->schemaFactory = $schemaFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function generate()
    {
        $schemaConfig = $this->typeGenerator->generateTypes();

        $config = $this->typeFactory->createObject([
            'name' => 'Query',
            'fields' => $schemaConfig['fields']
        ]);
        $schema = $this->schemaFactory->create(['query' => $config, 'types' => $schemaConfig['types']]);
        return $schema;
    }
}
