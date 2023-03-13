<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\Indexer;

use Magento\Framework\Indexer\Dimension;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;
use Magento\Store\Model\Store;
use SplFixedArray;
use Traversable;

class WebsiteDimensionProvider implements DimensionProviderInterface
{
    /**
     * Name for website dimension for multidimensional indexer
     * 'ws' - stands for 'website_store'
     */
    const DIMENSION_NAME = 'ws';

    /**
     * @var SplFixedArray
     */
    private $websitesDataIterator;

    /**
     * @param WebsiteCollectionFactory $collectionFactory
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        private readonly WebsiteCollectionFactory $collectionFactory,
        private readonly DimensionFactory $dimensionFactory
    ) {
    }

    /**
     * @return Dimension[]|Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->getWebsites() as $website) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$website);
        }
    }

    /**
     * @return array
     */
    private function getWebsites(): array
    {
        if ($this->websitesDataIterator === null) {
            $websites = $this->collectionFactory->create()
                ->addFieldToFilter('code', ['neq' => Store::ADMIN_CODE])
                ->getAllIds();
            $this->websitesDataIterator = is_array($websites) ? $websites : [];
        }

        return $this->websitesDataIterator;
    }
}
