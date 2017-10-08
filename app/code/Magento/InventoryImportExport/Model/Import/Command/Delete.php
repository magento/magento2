<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\Inventory\Model\ResourceModel\SourceItem\DeleteMultiple  as SourceItemsDelete;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * @inheritdoc
 */
class Delete implements CommandInterface
{

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var SourceItemsDelete
     */
    private $sourceItemsDelete;

    /**
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsDelete $sourceItemsSave
     */
    public function __construct(
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsDelete $sourceItemsDelete
    ) {
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsDelete = $sourceItemsDelete;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $bunch)
    {
        $sourceItems = [];

        foreach ($bunch as $rowNum => $rowData) {
            $sourceItems[]= $this->buildSourceItem($rowData);
        }

        $this->sourceItemsDelete->execute($sourceItems);
    }

    /**
     * @param array $rowData
     * @return SourceItemInterface
     */
    private function buildSourceItem(array $rowData)
    {
        /** @var SourceItemInterface $sourceItem */
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceId($rowData[Sources::COL_SOURCE]);

        return $sourceItem;
    }
}
