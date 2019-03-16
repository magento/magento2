<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Inventory\Model\ResourceModel\GetAvailableSourceItemsDataBySkusAndSortedSource;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\GetAvailableSourceItemsBySkusAndSortedSourceInterface;

/**
 * @inheritdoc
 */
class GetAvailableSourceItemsBySkusAndSortedSource implements GetAvailableSourceItemsBySkusAndSortedSourceInterface
{
    /**
     * @var GetAvailableSourceItemsDataBySkusAndSortedSource
     */
    private $getSourceItemsDataBySkusAndSortedSource;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemInterfaceFactory;

    /**
     * GetSourceItemsBySkusAndSortedSource constructor.
     * @param GetAvailableSourceItemsDataBySkusAndSortedSource $getSourceItemsDataBySkusAndSortedSource
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetAvailableSourceItemsDataBySkusAndSortedSource $getSourceItemsDataBySkusAndSortedSource,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory
    ) {
        $this->getSourceItemsDataBySkusAndSortedSource = $getSourceItemsDataBySkusAndSortedSource;
        $this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, array $sortedSourceCodes): array
    {
        $res = [];
        $sourceItemsData = $this->getSourceItemsDataBySkusAndSortedSource->execute($skus, $sortedSourceCodes);

        foreach ($sourceItemsData as $sourceItemData) {
            $res[] = $this->sourceItemInterfaceFactory->create(['data' => $sourceItemData]);
        }

        return $res;
    }
}
