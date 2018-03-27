<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Definition\OutputType;
use Magento\Framework\GraphQl\Config\ConfigInterface;
use Magento\Framework\GraphQl\Type\Output\OutputMapper;
use Magento\Framework\GraphQl\Type\Definition\ScalarTypes;

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
     * @var ScalarTypes
     */
    private $scalarTypes;

    /**
     * @var OutputType[]
     */
    private $typeObjects;

    /**
     * SchemaProvider constructor.
     * @param ConfigInterface $config
     * @param OutputMapper $outputMapper
     * @param ScalarTypes $scalarTypes
     */
    public function __construct(
        ConfigInterface $config,
        OutputMapper $outputMapper,
        ScalarTypes $scalarTypes
    ) {
        $this->config = $config;
        $this->outputMapper = $outputMapper;
        $this->scalarTypes = $scalarTypes;
    }

    /**
     * Retrieve all type objects generated for a GraphQL schema.
     *
     * @return OutputType[]
     */
    public function getTypes() : array
    {
        if ($this->typeObjects !== null) {
            return $this->typeObjects;
        }

        $this->typeObjects = [];
        foreach ($this->config->getDeclaredTypeNames() as $typeName) {
            if ($this->scalarTypes->isScalarType($typeName)) {
                $this->typeObjects[$typeName] = $this->scalarTypes->getScalarTypeInstance($typeName);
            } else {
                $this->typeObjects[$typeName] = $this->outputMapper->getOutputType($typeName);
            }
        }
        return $this->typeObjects;
    }
}
