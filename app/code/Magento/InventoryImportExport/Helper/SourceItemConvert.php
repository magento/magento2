<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Helper;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryImportExport\Model\Import\Sources;

class SourceItemConvert
{
    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @param SourceItemInterfaceFactory $sourceItemFactory
     */
    public function __construct(SourceItemInterfaceFactory $sourceItemFactory)
    {
        $this->sourceItemFactory = $sourceItemFactory;
    }

    /**
     * Converts a data in sourceItem list.
     * @param array $rowData
     * @return SourceItemInterface[]
     */
    public function convert(array $bunch): array
    {
        $sourceItems = [];
        foreach ($bunch as $rowNum => $rowData) {
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


            $sourceItems[] = $sourceItem;
        }

        return $sourceItems;
    }
}