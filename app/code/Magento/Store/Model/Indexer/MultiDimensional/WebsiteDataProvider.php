<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Indexer\MultiDimensional;

use Magento\Framework\Indexer\Dimension;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Store\Model\Store;

class WebsiteDataProvider implements DimensionProviderInterface
{
    /**
     * Name for website dimension for multidimensional indexer
     * 'ws' - stands for 'website_store'
     */
    const DIMENSION_NAME = 'ws';

    /**
     * @var WebsiteCollectionFactory
     */
    private $collectionFactory;

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
    public function __construct(WebsiteCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return Dimension[]|\Traversable
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->getWebsites() as $website) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$website);
        }
    }

    /**
     * @return \SplFixedArray
     */
    private function getWebsites()
    {
        if ($this->websitesDataIterator === null) {
            $this->websitesDataIterator = \SplFixedArray::fromArray(
                $this->collectionFactory->create()
                    ->addFieldToFilter('code', ['neq' => Store::ADMIN_CODE])
                    ->getAllIds(),
                false
            );
        }

        return $this->websitesDataIterator;
    }
}
