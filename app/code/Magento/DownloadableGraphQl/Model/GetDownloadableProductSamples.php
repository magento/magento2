<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableGraphQl\Model;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Model\ResourceModel\Sample\CollectionFactory;

/**
 * Returns samples of a particular downloadable product
 */
class GetDownloadableProductSamples
{
    /**
     * @var CollectionFactory
     */
    private $sampleCollectionFactory;

    /**
     * @param CollectionFactory $sampleCollectionFactory
     */
    public function __construct(
        CollectionFactory $sampleCollectionFactory
    ) {
        $this->sampleCollectionFactory = $sampleCollectionFactory;
    }

    /**
     * Returns downloadable product samples
     *
     * @param Product $product
     * @return SampleInterface[]
     */
    public function execute(Product $product): array
    {
        $samples = $this->sampleCollectionFactory->create()
            ->addTitleToResult($product->getStoreId())
            ->addProductToFilter($product->getId());
        return $samples->getItems();
    }
}
