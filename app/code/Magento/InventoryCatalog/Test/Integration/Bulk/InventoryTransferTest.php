<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\Bulk;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class InventoryTransferTest extends TestCase
{
    /**
     * @var BulkInventoryTransferInterface
     */
    private $bulkInventoryTransfer;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    public function setUp()
    {
        parent::setUp();
        $this->bulkInventoryTransfer = Bootstrap::getObjectManager()->get(BulkInventoryTransferInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @param string $sku
     * @return array
     */
    private function getSourceItemCodesBySku(string $sku): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $res = [];
        foreach ($sourceItems as $sourceItem) {
            $res[] = $sourceItem->getSourceCode();
        }

        return $res;
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return float
     */
    private function getSourceItemQuantity(string $sku, string $sourceCode): ?float
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        if (empty($sourceItems)) {
            return null;
        }

        return (float) reset($sourceItems)->getQuantity();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDbIsolation enabled
     */
    public function testBulkInventoryTransfer()
    {
        $skus = ['SKU-1', 'SKU-2'];
        $this->bulkInventoryTransfer->execute($skus, 'eu-3', false);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-2');
        self::assertNotContains(
            'eu-3',
            $sourceItemCodes,
            'Products are not transferred'
        );

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertContains(
            'eu-3',
            $sourceItemCodes,
            'Products are not transferred to sources they are not assigned to'
        );

        self::assertEquals(
            0,
            $this->getSourceItemQuantity('SKU-1', 'eu-1'),
            'Items were not removed from source during inventory transfer'
        );
        self::assertEquals(
            28.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-3'),
            'Items were not moved to destination source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDbIsolation enabled
     */
    public function testBulkInventoryTransferFromDefaultSource()
    {
        $skus = ['SKU-1'];
        $this->bulkInventoryTransfer->execute($skus, 'eu-3', true);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertContains(
            'eu-3',
            $sourceItemCodes,
            'Products are not transferred to sources they are not assigned to'
        );
        self::assertEquals(
            0,
            $this->getSourceItemQuantity('SKU-1', $this->defaultSourceProvider->getCode()),
            'Items were not removed from Default Source while using the Default Source Only option'
        );
        self::assertEquals(
            5.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-1'),
            'Items were removed from sources while using the Default Source Only option'
        );
        self::assertEquals(
            15.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-3'),
            'Items were not moved correctly while using the Default Source Only option'
        );
    }
}
