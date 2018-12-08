<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

/**
 * DTO to work with dimension modes
 */
class DimensionModes
{
    /**
     * @var DimensionMode[]
     */
    private $dimensions;

    /**
     * @param DimensionMode[] $dimensions
     */
    public function __construct(array $dimensions)
    {
        $this->dimensions = (function (DimensionMode ...$dimensions) {
            $result = [];
            foreach ($dimensions as $dimension) {
                $result[$dimension->getName()] = $dimension;
<<<<<<< HEAD
            };
=======
            }
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            return $result;
        })(...$dimensions);
    }

    /**
     * Returns dimensions and their modes
     *
     * @return array
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }
}
