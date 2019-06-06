<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api\Bulk;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/909116/scenarios/3042077
 */
class PartialInventoryTransferTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/inventory/bulk-partial-source-transfer';
    const VALIDATION_FAIL_MESSAGE = 'Transfer validation failed';

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

        $this->_webApiCall($serviceInfo, $this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-2'));

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

        $expectedError = [
            'message' => self::VALIDATION_FAIL_MESSAGE,
            'errors' => [
                [
                    'message' => 'Origin source %sourceCode does not exist',
                    'parameters' => [
                        'sourceCode' => 'eu-999'
                    ]
                ],
                [
                    'message' => '%message',
                    'parameters' => [
                        'message' => 'Source item for SKU-1 and eu-999 does not exist'
                    ]
                ]
            ]
        ];
        $this->webApiCallWithException($serviceInfo, $this->getTransferItem('SKU-1', 1, 'eu-999', 'eu-2'), $expectedError);
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

        $expectedError = [
            'message' => self::VALIDATION_FAIL_MESSAGE,
            'errors' => [
                [
                    'message' => 'Destination source %sourceCode does not exist',
                    'parameters' => [
                        'sourceCode' => 'eu-999'
                    ]
                ],
                [
                    'message' => '%message',
                    'parameters' => [
                        'message' => 'Source item for SKU-1 and eu-999 does not exist'
                    ]
                ]
            ]
        ];
        $this->webApiCallWithException($serviceInfo, $this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-999'), $expectedError);
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

        $expectedError = [
            'message' => self::VALIDATION_FAIL_MESSAGE,
            'errors' => [
                [
                    'message' => 'Cannot transfer a source on itself',
                    'parameters' => []
                ]
            ]
        ];
        $this->webApiCallWithException($serviceInfo, $this->getTransferItem('SKU-1', 1, 'eu-3', 'eu-3'), $expectedError);
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

        $expectedError = [
            'message' => self::VALIDATION_FAIL_MESSAGE,
            'errors' => [
                [
                    'message' => 'Requested transfer amount for sku %sku is not available',
                    'parameters' => [
                        'sku' => 'SKU-1'
                    ]
                ]
            ]
        ];
        $this->webApiCallWithException($serviceInfo, $this->getTransferItem('SKU-1', 100, 'eu-3', 'eu-2'), $expectedError);
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
            'items' => [
                [PartialInventoryTransferItemInterface::SKU => $sku, PartialInventoryTransferItemInterface::QTY => $qty]
            ],
            'origin_source_code' => $origin,
            'destination_source_code' => $destination
        ];
    }

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedError
     */
    private function webApiCallWithException(array $serviceInfo, array $data, array $expectedError): void
    {
        try {
            $this->_webApiCall($serviceInfo, $data);
            $this->fail('An exception is expected but not thrown.');
        } catch (\Exception $e) {
            self::assertEquals($expectedError, $this->processRestExceptionResult($e));
            self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
        }
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
