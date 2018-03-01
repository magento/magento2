<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss;

use Magento\Framework\Event\ManagerInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\SelectBuilder;

class GetAdaptedNotifyStockCollection
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var SelectBuilder
     */
    private $selectBuilder;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $eventManager
     * @param SelectBuilder $selectBuilder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ManagerInterface $eventManager,
        SelectBuilder $selectBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->eventManager = $eventManager;
        $this->selectBuilder = $selectBuilder;
    }

    /**
     * @return Collection
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToSelect(SourceItemInterface::SOURCE_CODE);
        $collection->addFieldToSelect(SourceItemInterface::QUANTITY, 'qty');

        $this->selectBuilder->build($collection->getSelect());

        $this->eventManager->dispatch(
            'rss_catalog_notify_stock_collection_select',
            ['collection' => $collection]
        );

        return $collection;
    }
}
