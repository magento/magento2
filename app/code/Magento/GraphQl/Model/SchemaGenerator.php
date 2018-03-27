<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GraphQl\Model;

use Magento\Framework\GraphQl\Type\SchemaFactory;
use Magento\Framework\GraphQl\Type\Schema;
use Magento\Framework\GraphQl\TypeFactory;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;

/**
 * Generate a query field and concrete types for GraphQL schema
 */
class SchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * @param TypeFactory $typeFactory
     * @param SchemaFactory $schemaFactory
     * @param OutputMapper $outputMapper
     */
    public function __construct(
        TypeFactory $typeFactory,
        SchemaFactory $schemaFactory,
        OutputMapper $outputMapper
    ) {
        $this->typeFactory = $typeFactory;
        $this->schemaFactory = $schemaFactory;
        $this->outputMapper = $outputMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function generate() : Schema
    {
        $schema = $this->schemaFactory->create(
            [
                'query' => $this->outputMapper->getOutputType('Query'),
                'typeLoader' => function ($name) {
                    return $this->outputMapper->getOutputType($name);
                }
            ]
        );
        return $schema;
    }
}
