<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

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
     * @param Alias $alias*
     * @throws LocalizedException
     */
    public function __construct(string $indexId, array $dimensions, Alias $alias)
    {
        foreach ($dimensions as $dimension) {
            if (!$dimension instanceof Dimension) {
                throw new LocalizedException(
                    new Phrase('Dimension have to be instance of Dimension class.')
                );
            }
        }

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
