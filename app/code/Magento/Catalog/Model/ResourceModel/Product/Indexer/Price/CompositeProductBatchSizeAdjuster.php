<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Correct batch size according to number of composite related items.
 */
class CompositeProductBatchSizeAdjuster implements CompositeProductBatchSizeAdjusterInterface
{
    /**
     * @var CompositeProductRelationsCalculator
     */
    private $compositeProductRelationsCalculator;

    /**
     * @param CompositeProductRelationsCalculator $compositeProductRelationsCalculator
     */
    public function __construct(CompositeProductRelationsCalculator $compositeProductRelationsCalculator)
    {
        $this->compositeProductRelationsCalculator = $compositeProductRelationsCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function adjust($batchSize)
    {
        $maxRelationsCount = $this->compositeProductRelationsCalculator->getMaxRelationsCount();
        return $maxRelationsCount > 0 ? ceil($batchSize / $maxRelationsCount) : $batchSize;
    }
}
