<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl;

use GraphQL\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;

/**
 * Container for retrieving generated type object representations of a GraphQL Schema.
 */
class SchemaProvider
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OutputMapper
     */
    private $outputMapper;

    /**
     * SchemaProvider constructor.
     * @param ConfigInterface $config
     * @param OutputMapper $outputMapper
     */
    public function __construct(
        ConfigInterface $config,
        OutputMapper $outputMapper
    ) {
        $this->config = $config;
        $this->outputMapper = $outputMapper;
    }

    /**
     * Retrieve all type objects generated for a GraphQL schema.
     *
     * @return array
     */
    public function getTypes() : array
    {
        $types = [];
        foreach ($this->config->getDeclaredTypeNames() as $typeName) {
            $types[$typeName] = $this->outputMapper->getTypeObject($typeName);
        }
        return $types;
    }

    /**
     * Retrieve the top-level Query type object containing all described queries for a client to consume.
     *
     * @return OutputType
     */
    public function getQuery() : OutputType
    {
        return $this->outputMapper->getTypeObject('Query');
    }
}
