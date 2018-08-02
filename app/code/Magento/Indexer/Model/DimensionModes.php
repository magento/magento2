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
        $this->validateDimensions($dimensions);
        $this->dimensions = $dimensions;
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

    /**
     * Validate dimensions.
     *
     * @param DimensionMode[] $dimensions
     * @throws \InvalidArgumentException
     */
    private function validateDimensions(array $dimensions)
    {
        foreach ($dimensions as $name => $dimension) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException(
                    (string)new \Magento\Framework\Phrase(
                        sprintf('Dimension name must be a string')
                    )
                );
            }
            if (!$dimension instanceof \Magento\Indexer\Model\DimensionMode) {
                throw new \InvalidArgumentException(
                    (string)new \Magento\Framework\Phrase(
                        sprintf('Dimension must be an instance of %s', \Magento\Indexer\Model\DimensionMode::class)
                    )
                );
            }
        }
    }
}
