<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\GraphQl\Config\Data\Mapper\StructureMapperInterface;
use Magento\Framework\GraphQl\Config\Data\StructureInterface;

/**
 * Provides access to typing information for a configured GraphQL schema.
 */
class Config implements ConfigInterface
{

    /**
     * @var DataInterface
     */
    private $data;

    /**
     * @var StructureMapperInterface[]
     */
    private $mappers;

    /**
     * @param DataInterface $data
     * @param StructureMapperInterface[] $mappers
     */
    public function __construct(
        DataInterface $data,
        array $mappers
    ) {
        $this->data = $data;
        $this->mappers = $mappers;
    }

    /**
     * Get a data object with data pertaining to a GraphQL type's structural makeup.
     *
     * @param string $key
     * @return StructureInterface
     * @throws \LogicException
     */
    public function getTypeStructure(string $key) : StructureInterface
    {
        $data = $this->data->get($key);
        if (!isset($data['type'])) {
            throw new \LogicException(sprintf('Type %s not declared in GraphQL schema', $key));
        }
        return $this->mappers[$data['type']]->map($data);
    }

    /**
     * Return all type names declared in a GraphQL schema's configuration.
     *
     * @return string[]
     */
    public function getDeclaredTypeNames() : array
    {
        $types = [];
        foreach ($this->data->get(null) as $item) {
            if (isset($item['type']) && $item['type'] == 'graphql_type') {
                $types[] = $item['name'];
            }
        }
        return $types;
    }
}
