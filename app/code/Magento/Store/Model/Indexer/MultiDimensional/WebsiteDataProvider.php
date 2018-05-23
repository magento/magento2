<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Indexer\MultiDimensional;

use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionDataProviderInterface;

class WebsiteDataProvider implements DimensionDataProviderInterface
{
    /**
     * Name for website dimension for multidimensional indexer
     * 'ws' - stands for 'website_store'
     */
    const DIMENSION_NAME = 'ws';

    /**
     * @var \SplFixedArray
     */
    private $websitesDataIterator;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param WebsiteCollectionFactory $collectionFactory
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(WebsiteCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory){
        $this->dimensionFactory = $dimensionFactory;
        $this->websitesDataIterator = \SplFixedArray::fromArray(
            $collectionFactory->create()->getAllIds()
        );
    }

    public function current()
    {
        return $this->dimensionFactory->create(self::DIMENSION_NAME, $this->websitesDataIterator->current());
    }

    public function next()
    {
        $this->websitesDataIterator->next();
    }

    public function key()
    {
        return $this->websitesDataIterator->key();
    }

    public function valid()
    {
        return $this->websitesDataIterator->valid();
    }

    public function rewind()
    {
        $this->websitesDataIterator->rewind();
    }
}
