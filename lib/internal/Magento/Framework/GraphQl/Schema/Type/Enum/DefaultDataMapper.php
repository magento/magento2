<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Enum;

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
     * @inheritdoc
     */
    public function getMappedEnums(string $enumName) : array
    {
        return $this->map[$enumName] ?? [];
    }
}
