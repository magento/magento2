<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DeleteMultipleTest extends TestCase
{

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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testDeleteMultipleWithEmptySourceItems()
    {
        $expectedCount = count($this->getSourceItems());

        $this->deleteModel->execute([]);

        $this->assertCount($expectedCount, $this->getSourceItems());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testDeleteMultipleForTwoItems()
    {
        $sourceItems = $this->getSourceItems();
        $expectedCount = count($sourceItems) - 2;
        $itemsToDelete = array_slice($sourceItems, 0, 2);
        $expectedResult = array_slice($sourceItems, 2);

        $this->deleteModel->execute($itemsToDelete);

        $result = array_values($this->getSourceItems());
        $this->assertCount($expectedCount, $result);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    private function getSourceItems(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->sourceItemRepository->getList($searchCriteria)->getItems();
    }
}
