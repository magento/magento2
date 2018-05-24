<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Indexer\MultiDimensional;

use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;

class WebsiteDataProvider implements DimensionProviderInterface
{
    /**
     * Name for website dimension for multidimensional indexer
     * 'ws' - stands for 'website_store'
     */
    const DIMENSION_NAME = 'ws';

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, DimensionFactory $dimensionFactory){
        $this->dimensionFactory = $dimensionFactory;
        $this->storeManager = $storeManager;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->storeManager->getWebsites(false) as $website) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, $website->getId());
        }
    }

    public function count(): int
    {
        return $this->websitesDataIterator->count();
    }
}
