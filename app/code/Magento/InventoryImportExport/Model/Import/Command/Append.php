<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Import\Command;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * @inheritdoc
 */
class Append implements CommandInterface
{
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $bunch)
    {
        $sourceItems = [];
        foreach ($bunch as $rowNum => $rowData) {
            $sourceItems[] = $this->buildSourceItem($rowData);
        }
        $this->sourceItemsSave->execute($sourceItems);
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
        $sourceItem->setSku($rowData[Sources::COL_SKU]);
        $sourceItem->setQuantity($rowData[Sources::COL_QTY]);

        $status = (int)$rowData[Sources::COL_QTY] > 0;
        if (isset($rowData[Sources::COL_STATUS])) {
            $status = (int)$rowData[Sources::COL_STATUS];
        }
        $sourceItem->setStatus($status);

        return $sourceItem;
    }
}
