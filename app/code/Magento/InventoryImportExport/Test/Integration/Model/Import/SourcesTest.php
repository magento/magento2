<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Model\Import;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryImportExport\Model\Import\Sources;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * TODO: fixture via composer
 */
class SourcesTest extends TestCase
{
    /**
     * @var Sources
     */
    private $importer;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $importDataMock;

    protected function setUp()
    {
        $this->importDataMock = $this->getMockBuilder(ImportData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->importer = Bootstrap::getObjectManager()->create(Sources::class, [
            'importData' => $this->importDataMock
        ]);

        $this->sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testValidateRowExpectsInvalidRow()
    {
        $rowData = $this->buildRowDataArray(880, 'SKU-55', 33, 1);
        $result = $this->importer->validateRow($rowData, 2);
        $this->assertNotTrue($result, 'Expect result FALSE as given source ID is not present in database.');
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testValidateRowExpectsValidRow()
    {
        $rowData = $this->buildRowDataArray(20, 'SKU-55', 33, 1);
        $result = $this->importer->validateRow($rowData, 2);
        $this->assertTrue($result, 'Expect result TRUE as given data is valid.');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testImportDataWithWrongBehavior()
    {
        $this->importer->setParameters([
            'behavior' => 'WrongBehavior'
        ]);

        $bunch = [
            $this->buildRowDataArray(10, 'SKU-1', 6.88, 1)
        ];
        $this->importDataMock->expects($this->any())
            ->method('getNextBunch')
            ->will($this->onConsecutiveCalls($bunch, false));

        $this->importer->importData();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testImportDataWithAppendBehavior()
    {
        $this->importer->setParameters([
            'behavior' => Import::BEHAVIOR_APPEND
        ]);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
        $beforeImportData = $this->buildDataArray($sourceItems->getItems());

        $bunch = [
            $this->buildRowDataArray(10, 'SKU-1', 6.8800, 1),
            $this->buildRowDataArray(20, 'SKU-1', 5.0000, 1),
            $this->buildRowDataArray(50, 'SKU-2', 15, 1),
            $this->buildRowDataArray(10, 'SKU-2', 33, 1),
        ];
        $this->importDataMock->expects($this->any())
            ->method('getNextBunch')
            ->will($this->onConsecutiveCalls($bunch, false));

        $this->importer->importData();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
        $expectedData = $this->updateDataArrayByBunch($beforeImportData, $bunch);
        $afterImportData = $this->buildDataArray($sourceItems->getItems());

        $this->assertEquals($expectedData, $afterImportData);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testImportDataWithDelteBehavior()
    {
        $this->importer->setParameters([
            'behavior' => Import::BEHAVIOR_DELETE
        ]);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $bunch = [
            $this->buildRowDataArray(10, 'SKU-1', 6.88, 1),
            $this->buildRowDataArray(20, 'SKU-1', 5, 1),
        ];
        $this->importDataMock->expects($this->any())
            ->method('getNextBunch')
            ->will($this->onConsecutiveCalls($bunch, false));

        $this->importer->importData();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
        $afterImportData = $this->buildDataArray($sourceItems->getItems());

        $this->assertArrayNotHasKey('10-SKU-1', $afterImportData);
        $this->assertArrayNotHasKey('20-SKU-1', $afterImportData);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testImportDataWithReplaceBehavior()
    {
        /** @see \Magento\InventoryImportExport\Model\Import\Command\Replace::execute */
        $this->importer->setParameters([
            'behavior' => Import::BEHAVIOR_REPLACE
        ]);

        $bunch = [
            $this->buildRowDataArray(20, 'SKU-1', 5, 1),
            $this->buildRowDataArray(50, 'SKU-2', 15, 1),
        ];
        $this->importDataMock->expects($this->any())
            ->method('getNextBunch')
            ->will($this->onConsecutiveCalls($bunch, false));

        $this->importer->importData();

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
        $afterImportData = $this->buildDataArray($sourceItems->getItems());

        $this->assertArrayHasKey('20-SKU-1', $afterImportData);
        $this->assertArrayHasKey('50-SKU-2', $afterImportData);
    }

    /**
     * @param int $sourceID
     * @param string $sku
     * @param int $qty
     * @param int $status
     * @return array
     */
    private function buildRowDataArray($sourceID, $sku, $qty, $status)
    {
        return [
            Sources::COL_SOURCE => $sourceID,
            Sources::COL_SKU => $sku,
            Sources::COL_QTY => $qty,
            Sources::COL_STATUS => $status,
        ];
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
     * @param array $data
     * @param array $bunch
     * @return array
     */
    private function updateDataArrayByBunch(array $data, array $bunch)
    {
        foreach ($bunch as $bunchData) {
            $key = sprintf('%s-%s', $bunchData[Sources::COL_SOURCE], $bunchData[Sources::COL_SKU]);
            $data[$key] = $this->buildRowDataArray(
                $bunchData[Sources::COL_SOURCE],
                $bunchData[Sources::COL_SKU],
                number_format($bunchData[Sources::COL_QTY], 4),
                $bunchData[Sources::COL_STATUS]
            );
        }
        return $data;
    }
}
