<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema;

use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Schema;
use Magento\Framework\GraphQl\Schema\Type\Input\InputMapper;
use Magento\Framework\GraphQl\Schema\Type\Output\OutputMapper;
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
     * @var InputMapper
     */
    private $inputMapper;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param SchemaFactory $schemaFactory
     * @param OutputMapper $outputMapper
     * @param InputMapper $inputMapper
     * @param ConfigInterface $config
     */
    public function __construct(
        SchemaFactory $schemaFactory,
        OutputMapper $outputMapper,
        InputMapper $inputMapper,
        ConfigInterface $config
    ) {
        $this->schemaFactory = $schemaFactory;
        $this->outputMapper = $outputMapper;
        $this->inputMapper = $inputMapper;
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
                'types' => $this->getTypes()
            ]
        );
        return $schema;
    }

    /**
     * @return array
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    private function getTypes()
    {
        $typesImplementors = [];
        foreach ($this->config->getDeclaredTypeNames() as $type) {
            switch ($type['type']) {
                case 'graphql_type' :
                    $typesImplementors [] = $this->outputMapper->getOutputType($type['name']);
                    break;
                case 'graphql_input' :
                    $typesImplementors [] = $this->inputMapper->getInputType($type['name']);
                    break;
            }
        }

        return $typesImplementors;
    }
}
