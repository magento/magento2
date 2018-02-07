<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MultiDimensionalIndexer;

/**
 * Index Name object
 *
 * @api
 */
class IndexName
{
    /**
     * @var string
     */
    private $indexId;

    /**
     * @var Dimension[]
     */
    private $dimensions;

    /**
     * @var Alias
     */
    private $alias;

    /**
     * @param string $indexId
     * @param Dimension[] $dimensions
     * @param Alias $alias
     */
    public function __construct(string $indexId, array $dimensions, Alias $alias)
    {
        $this->indexId = $indexId;
        $this->dimensions = $dimensions;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getIndexId(): string
    {
        return $this->indexId;
    }

    /**
     * @return Dimension[]
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * @return Alias
     */
    public function getAlias(): Alias
    {
        return $this->alias;
    }
}
