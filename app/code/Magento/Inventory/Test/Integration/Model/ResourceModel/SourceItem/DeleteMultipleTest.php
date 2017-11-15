<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Model\ResourceModel\SourceItem;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\ResourceModel\SourceItem\DeleteMultiple;
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
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    private function getSourceItems(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->sourceItemRepository->getList($searchCriteria)->getItems();
    }
}
