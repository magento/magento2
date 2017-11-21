<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Model;

use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class StockItemImporterTest extends TestCase
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProviderInterface;
    
    /**
     * @var StockItemImporterInterface
     */
    private $importerInterface;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepositoryInterface;

    /**
     * Setup Test for Stock Item Importer
     */
    public function setUp()
    {
        $this->defaultSourceProviderInterface = Bootstrap::getObjectManager()->get(
            DefaultSourceProviderInterface::class
        );
        $this->importerInterface = Bootstrap::getObjectManager()->get(
            StockItemImporterInterface::class
        );
        $this->searchCriteriaBuilderFactory = Bootstrap::getObjectManager()->get(
            SearchCriteriaBuilderFactory::class
        );
        $this->sourceItemRepositoryInterface = Bootstrap::getObjectManager()->get(
            SourceItemRepositoryInterface::class
        );
    }

    /**
     * Tests Source Item Import of default source
     *
     * @magentoDbIsolation enabled
     */
    public function testSourceItemImportWithDefaultSource()
    {
        $stockData = [
            'sku' => 'SKU-1',
            'qty' => 1,
            'is_in_stock' => SourceItemInterface::STATUS_IN_STOCK
        ];

        $this->importerInterface->import([$stockData]);

        $compareData = $this->buildDataArray($this->getSourceItemList()->getItems());
        $expectedData = [
            SourceItemInterface::SKU => $stockData['sku'],
            SourceItemInterface::QUANTITY => '1.0000',
            SourceItemInterface::SOURCE_ID => (string) $this->defaultSourceProviderInterface->getId(),
            SourceItemInterface::STATUS => (string) SourceItemInterface::STATUS_IN_STOCK
        ];

        $this->assertArrayHasKey('SKU-1', $compareData);
        $this->assertSame($expectedData, $compareData['SKU-1']);
    }

    /**
     * Get List of Source Items which match SKU and Source ID of dummy data
     *
     * @return \Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface
     */
    private function getSourceItemList()
    {
        /** @var SearchCriteriaBuilder $searchCriteria */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteriaBuilder->addFilter(
            SourceItemInterface::SKU,
            'SKU-1'
        );

        $searchCriteriaBuilder->addFilter(
            SourceItemInterface::SOURCE_ID,
            $this->defaultSourceProviderInterface->getId()
        );

        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $searchCriteriaBuilder->create();
        return $this->sourceItemRepositoryInterface->getList($searchCriteria);
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return array
     */
    private function buildDataArray(array $sourceItems)
    {
        $comparableArray = [];
        foreach ($sourceItems as $sourceItem) {
            $comparableArray[$sourceItem->getSku()] = [
                SourceItemInterface::SKU => $sourceItem->getSku(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::SOURCE_ID => $sourceItem->getSourceId(),
                SourceItemInterface::STATUS => $sourceItem->getStatus()
            ];
        }
        return $comparableArray;
    }
}
