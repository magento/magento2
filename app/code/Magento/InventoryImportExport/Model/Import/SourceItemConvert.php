<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class SourceItemConvert
{
    /**´
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
     * @param array $bunch
     * @return SourceItemInterface[]
     */
    public function convert(array $bunch): array
    {
        $sourceItems = [];
        foreach ($bunch as $rowData) {
            /** @var SourceItemInterface $sourceItem */
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode($rowData[Sources::COL_SOURCE_CODE]);
            $sourceItem->setSku($rowData[Sources::COL_SKU]);
            $sourceItem->setQuantity((float)$rowData[Sources::COL_QTY]);

            $status = (int)($rowData[Sources::COL_QTY] > 0);
            if (isset($rowData[Sources::COL_STATUS])) {
                $status = (int)$rowData[Sources::COL_STATUS];
            }
            $sourceItem->setStatus($status);

            $sourceItems[] = $sourceItem;
        }

        return $sourceItems;
    }
}
