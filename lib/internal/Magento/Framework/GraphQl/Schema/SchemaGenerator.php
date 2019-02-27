<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema;

use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;
use Magento\Framework\GraphQl\SchemaFactory;

/**
 * Generate a query field and concrete types for GraphQL schema
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param SchemaFactory $schemaFactory
     * @param ConfigInterface $config
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(
        SchemaFactory $schemaFactory,
        ConfigInterface $config,
        TypeRegistry $typeRegistry
    ) {
        $this->schemaFactory = $schemaFactory;
        $this->config = $config;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritdoc
     */
    public function generate() : Schema
    {
        $schema = $this->schemaFactory->create(
            [
                'query' => $this->typeRegistry->get('Query'),
                'mutation' => $this->typeRegistry->get('Mutation'),
                'typeLoader' => function ($name) {
                    return $this->typeRegistry->get($name);
                },
                'types' => function () {
                    $typesImplementors = [];
                    foreach ($this->config->getDeclaredTypes() as $type) {
                        $typesImplementors [] = $this->typeRegistry->get($type['name']);
                    }
                    return $typesImplementors;
                }
            ]
        );
        return $schema;
    }
}
