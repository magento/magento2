<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Entity;

/**
 * Default implementation for taking GraphQL types configured to be mapped to Resource Model entity names in the DI.
 */
class DefaultMapper implements MapperInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * {@inheritDoc}
     */
    public function getMappedTypes(string $entityName)
    {
        return isset($this->map[$entityName]) ? $this->map[$entityName] : [];
    }
}
