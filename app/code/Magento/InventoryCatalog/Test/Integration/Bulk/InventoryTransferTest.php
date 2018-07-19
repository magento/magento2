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
    public function testBulkInventoryTransferAndUnassign()
    {
        $skus = ['SKU-1'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->bulkInventoryTransfer->execute($skus, 'eu-1', 'eu-2', true);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertNotContains(
            'eu-1',
            $sourceItemCodes,
            'Products are not unassigned from origin source'
        );

        self::assertEquals(
            8.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-2'),
            'Items were not correctly moved to destination source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDbIsolation enabled
     */
    public function testBulkInventoryTransferToNewSource()
    {
        $skus = ['SKU-1'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->bulkInventoryTransfer->execute($skus, 'eu-1', 'us-1', false);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertContains(
            'us-1',
            $sourceItemCodes,
            'Products are not assigned to a new source if transferred'
        );

        self::assertEquals(
            0,
            $this->getSourceItemQuantity('SKU-1', 'eu-1'),
            'Items were not removed from source during inventory transfer'
        );

        self::assertEquals(
            5.5,
            $this->getSourceItemQuantity('SKU-1', 'us-1'),
            'Items were not correctly moved to destination source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDbIsolation enabled
     */
    public function testBulkInventoryTransferFromUnassignedSourceSource()
    {
        $skus = ['SKU-1'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->bulkInventoryTransfer->execute($skus, 'us-1', 'eu-1', false);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertNotContains(
            'us-1',
            $sourceItemCodes,
            'Products are assigned to origin source even if they were not'
        );

        self::assertEquals(
            5.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-1'),
            'Destination source is changed even if origin source was not assigned'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDbIsolation enabled
     */
    public function testBulkInventoryTransferToAssignedSource()
    {
        $skus = ['SKU-1'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->bulkInventoryTransfer->execute($skus, 'eu-1', 'eu-2', false);

        $sourceItemCodes = $this->getSourceItemCodesBySku('SKU-1');
        self::assertContains(
            'eu-2',
            $sourceItemCodes,
            'Products are not assigned to destination source'
        );

        self::assertEquals(
            0,
            $this->getSourceItemQuantity('SKU-1', 'eu-1'),
            'Items were not removed from source during inventory transfer'
        );

        self::assertNotEquals(
            5.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-2'),
            'Item quantity on destination source is not incremented by origin source'
        );

        self::assertEquals(
            8.5,
            $this->getSourceItemQuantity('SKU-1', 'eu-2'),
            'Items were not correctly moved to destination source'
        );
    }
}
