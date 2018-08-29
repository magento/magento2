<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema;

use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\Schema;
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
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param SchemaFactory $schemaFactory
     * @param OutputMapper $outputMapper
     * @param ConfigInterface $config
     */
    public function __construct(
        SchemaFactory $schemaFactory,
        OutputMapper $outputMapper,
        ConfigInterface $config
    ) {
        $this->schemaFactory = $schemaFactory;
        $this->outputMapper = $outputMapper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate() : Schema
    {
        $schema = $this->schemaFactory->create(
            [
                'query' => $this->outputMapper->getOutputType('Query'),
                'mutation' => $this->outputMapper->getOutputType('Mutation'),
                'typeLoader' => function ($name) {
                    return $this->outputMapper->getOutputType($name);
                },
                'types' => function () {
                    //all types should be generated only on introspection
                    $typesImplementors = [];
                    foreach ($this->config->getDeclaredTypeNames() as $name) {
                        $typesImplementors [] = $this->outputMapper->getOutputType($name);
                    }
                    return $typesImplementors;
                }
            ]
        );
        return $schema;
    }
}
