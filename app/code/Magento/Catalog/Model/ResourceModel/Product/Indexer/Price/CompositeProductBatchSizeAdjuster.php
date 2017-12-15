<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
