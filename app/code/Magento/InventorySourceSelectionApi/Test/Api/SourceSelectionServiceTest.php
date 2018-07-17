<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Test\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;

class SourceSelectionServiceTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/source-selection-algorithm-result';
    const SERVICE_NAME = 'inventorySourceSelectionApiSourceSelectionServiceV1';
    /**#@-*/

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $defaultAlgorithmCode;

    protected function setUp()
    {
        parent::setUp();
        $this->defaultAlgorithmCode = Bootstrap::getObjectManager()->get(
            GetDefaultSourceSelectionAlgorithmCodeInterface::class
        );
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testSourceSelectionService()
    {
        $inventoryRequest = [
            'stockId' => 10,
            'items' => [
                [
                    'sku' => 'SKU-1',
                    'qty' => 8
                ],
                [
                    'sku' => 'SKU-4',
                    'qty' => 4
                ]
            ]
        ];

        $expectedResultData = [
            'source_selection_items' => [
                [
                    'source_code' => 'eu-1',
                    'sku' => 'SKU-1',
                    'qty_to_deduct' => 5.5,
                    'qty_available' => 5.5
                ],
                [
                    'source_code' => 'eu-2',
                    'sku' => 'SKU-1',
                    'qty_to_deduct' => 2.5,
                    'qty_available' => 3
                ],
                [
                    'source_code' => 'eu-2',
                    'sku' => 'SKU-4',
                    'qty_to_deduct' => 4,
                    'qty_available' => 6
                ],
            ],
            'shippable' => 1
        ];

        $algorithmCode = $this->defaultAlgorithmCode->execute();
        $requestData = [
            'inventoryRequest' => $inventoryRequest,
            'algorithmCode' => $algorithmCode
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
            ],
        ];

        $sourceSelectionAlgorithmResult = (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo, $requestData)
            : $this->_webApiCall($serviceInfo, $requestData);

        self::assertInternalType('array', $sourceSelectionAlgorithmResult);
        self::assertNotEmpty($sourceSelectionAlgorithmResult);
        AssertArrayContains::assert($expectedResultData, $sourceSelectionAlgorithmResult);
    }
}
