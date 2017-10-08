<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DeleteMultipleTest extends TestCase
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var DeleteMultiple
     */
    private $deleteModel;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    protected function setUp()
    {
        $this->deleteModel = Bootstrap::getObjectManager()->create(DeleteMultiple::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testDeleteMultipleWithEmptySourceItems()
    {
        $beforeDeleteMultiple = $this->getSourceItems()->getTotalCount();

        $sourceItems = [];
        $this->deleteModel->execute($sourceItems);

        $afterDeleteMultiple = $this->getSourceItems()->getTotalCount();
        $this->assertEquals($beforeDeleteMultiple, $afterDeleteMultiple);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testDeleteMultipleForTwoItems()
    {
        $sourceItems = $this->getSourceItems();
        $beforeDeleteMultiple = $this->buildDataArray($sourceItems->getItems());
        $itemsToDelete = array_slice($beforeDeleteMultiple, 0, 2);
        $expectedData = array_slice($beforeDeleteMultiple, 2);

        $this->deleteModel->execute($itemsToDelete);

        $sourceItems = $this->getSourceItems();
        $afterDeleteMultiple = $this->buildDataArray($sourceItems->getItems());
        $this->assertEquals($expectedData, $afterDeleteMultiple);
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return array
     */
    private function buildDataArray(array $sourceItems)
    {
        $comparableArray = [];
        foreach ($sourceItems as $sourceItem) {
            $key = sprintf('%s-%s', $sourceItem->getSourceId(), $sourceItem->getSku());
            $comparableArray[$key] = $this->buildRowDataArray(
                $sourceItem->getSourceId(),
                $sourceItem->getSku(),
                $sourceItem->getQuantity(),
                $sourceItem->getStatus()
            );
        }
        return $comparableArray;
    }


    /**
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    private function getSourceItems(): SourceItemSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->sourceItemRepository->getList($searchCriteria);
    }
}
