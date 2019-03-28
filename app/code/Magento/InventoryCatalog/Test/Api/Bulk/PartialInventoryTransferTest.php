<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api\Bulk;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class PartialInventoryTransferTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/bulk-partial-source-transfer';

    /** @var SourceItemRepositoryInterface */
    private $sourceItemRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    public function setUp()
    {
        $this->sourceItemRepository  = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testValidTransfer()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST
            ]
        ];

        $this->_webApiCall($serviceInfo, ['items' => [$this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-2')]]);

        $originSourceItem = $this->getSourceItem('SKU-1', 'eu-3');
        $destinationSourceItem = $this->getSourceItem('SKU-1', 'eu-2');

        if ($originSourceItem === null || $destinationSourceItem === null) {
            $this->fail('Both source items should exist.');
        }

        $this->assertEquals(9, $originSourceItem->getQuantity());
        $this->assertEquals(4, $destinationSourceItem->getQuantity());
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testInvalidTransferOrigin()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, ['items' => [$this->getTransferItem('SKU-1', 1, 'eu-999', 'eu-2')]]);
        $this->assertEquals(1, count($result));

        $destinationItem = array_shift($result);
        $this->assertEquals(3, $destinationItem[SourceItemInterface::QUANTITY]);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testInvalidTransferDestination()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, ['items' => [$this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-999')]]);
        $this->assertEquals(1, count($result));

        $originItem = array_shift($result);
        $this->assertEquals(10, $originItem[SourceItemInterface::QUANTITY]);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testInvalidTransferOriginAndDestinationAreTheSame()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST
            ]
        ];

        $result = $this->_webApiCall($serviceInfo, ['items' => [$this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-3')]]);
        $this->assertEquals(1, count($result));

        $originItem = array_shift($result);
        $this->assertEquals(10, $originItem[SourceItemInterface::QUANTITY]);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testInvalidTransferQuantityGreaterThanAvailable()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST
            ]
        ];

        $this->_webApiCall($serviceInfo, ['items' => [$this->getTransferItem('SKU-1', 100, 'eu-3', 'eu-2')]]);

        $originSourceItem = $this->getSourceItem('SKU-1', 'eu-3');
        $destinationSourceItem = $this->getSourceItem('SKU-1', 'eu-2');

        if ($originSourceItem === null || $destinationSourceItem === null) {
            $this->fail('Both source items should exist.');
        }

        $this->assertEquals(10, $originSourceItem->getQuantity());
        $this->assertEquals(3, $destinationSourceItem->getQuantity());
    }

    /**
     * @param string $sku
     * @param float $qty
     * @param string $origin
     * @param string $destination
     * @return array
     */
    private function getTransferItem(string $sku, float $qty, string $origin, string $destination): array
    {
        return [
            PartialInventoryTransferInterface::SKU => $sku,
            PartialInventoryTransferInterface::QTY => $qty,
            PartialInventoryTransferInterface::ORIGIN_SOURCE_CODE => $origin,
            PartialInventoryTransferInterface::DESTINATION_SOURCE_CODE => $destination
        ];
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return SourceItemInterface|null
     */
    private function getSourceItem(string $sku, string $sourceCode): ?SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        return empty($sourceItems) ? null : array_shift($sourceItems);
    }
}
