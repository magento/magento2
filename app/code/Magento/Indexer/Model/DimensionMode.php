<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

/**
 * DTO to work with dimension mode
 */
class DimensionMode
{
    /**
     * @var array
     */
    private $name;

    /**
     * @var array
     */
    private $dimensions;

    /**
     * @param string $name
     * @param array  $dimensions
     */
    public function __construct(string $name, array $dimensions)
    {
        $this->dimensions = (function (string ...$dimensions) {
            return $dimensions;
        })(...$dimensions);
        $this->name = $name;
    }

    /**
     * Returns dimension name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns dimension modes
     *
     * @return string[]
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }
}
