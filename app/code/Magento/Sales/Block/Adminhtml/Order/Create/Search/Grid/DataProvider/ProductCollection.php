<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\Store;

/**
 * Prepares product collection for the grid
 */
class ProductCollection
{
    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ProductCollectionFactory $collectionFactory
     */
    public function __construct(
        ProductCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Provide products collection filtered with store
     *
     * @param Store $store
     * @return Collection
     */
    public function getCollectionForStore(Store $store):Collection
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $collection->setStore($store);
        $collection->addAttributeToSelect(
            'gift_message_available'
        );
        $collection->addAttributeToSelect(
            'sku'
        );
        $collection->addStoreFilter();

        return $collection;
    }
}
