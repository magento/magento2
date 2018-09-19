<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Entity;

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
    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    /**
     * @inheritdoc
     */
    public function getMappedTypes(string $entityName) : array
    {
        return $this->map[$entityName] ?? [];
    }
}
