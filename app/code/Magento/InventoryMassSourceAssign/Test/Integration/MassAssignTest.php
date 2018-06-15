<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssign\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryMassSourceAssign\Model\SourceItemsBuilder;
use Magento\InventoryMassSourceAssignApi\Api\MassAssignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class MassAssignTest extends TestCase
{
    /**
     * @var MassAssignInterface
     */
    private $massAssign;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceItemsBuilder
     */
    private $sourceItemsBuilder;

    public function setUp()
    {
        parent::setUp();
        $this->massAssign = Bootstrap::getObjectManager()->get(MassAssignInterface::class);
        $this->sourceItemsBuilder = Bootstrap::getObjectManager()->get(SourceItemsBuilder::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDbIsolation enabled
     */
    public function testMassSourceAssignment()
    {
        $skus = ['SKU-1', 'SKU-2'];
        $sources = ['eu-1'];
        $count = $this->massAssign->execute($skus, $sources);

        self::assertEquals(
            2,
            $count,
            'Products source assignment count do not match'
        );

        foreach ($skus as $sku) {
            $sourceItemCodes = $this->getSourceItemCodesBySku($sku);
            self::assertContains(
                'eu-1',
                $sourceItemCodes,
                'Mass source assignment failed with a single source item'
            );
        }

        $skus = ['SKU-1', 'SKU-2'];
        $sources = ['eu-1'];
        $count = $this->massAssign->execute($skus, $sources);

        self::assertEquals(
            0,
            $count,
            'Source items are created in mass assignment operation even if they were existing'
        );

        $skus = ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4'];
        $sources = ['eu-1', 'eu-2', 'eu-3'];
        $count = $this->massAssign->execute($skus, $sources);

        foreach ($skus as $sku) {
            $sourceItemCodes = $this->getSourceItemCodesBySku($sku);

            foreach ($sources as $source) {
                self::assertContains(
                    $source,
                    $sourceItemCodes,
                    'Mass source assignment failed with multiple source items'
                );
            }
        }

        self::assertEquals(
            10, // (4skus x 3source) - (2previous_assignment) = 10
            $count,
            'Products source assignment count do not match with overlapping sources'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/products_all_types.php
     * @magentoDbIsolation enabled
     */
    public function testMassSourceAssignmentOnMixedProducts()
    {
        $skus = ['simple_sku', 'configurable_sku', 'virtual_sku', 'grouped_sku', 'bundle_sku', 'downloadable_sku'];
        $sources = ['eu-1', 'eu-2', 'eu-3'];
        $count = $this->massAssign->execute($skus, $sources);

        self::assertEquals(
            9, // (simple + downloadable + virtual) x (eu-1, eu-2, eu-3) = 9
            $count,
            'Products source assignment count do not match with mixed product types'
        );
    }
}
