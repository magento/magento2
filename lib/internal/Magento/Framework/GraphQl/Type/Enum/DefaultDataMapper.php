<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Type\Enum;

/**
 * Default implementation for taking GraphQL enum types and map them to values by declaring an array map in the DI.
 */
class DefaultDataMapper implements DataMapperInterface
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
    public function getMappedEnums(string $enumName)
    {
        return isset($this->map[$enumName]) ? $this->map[$enumName] : [];
    }
}
