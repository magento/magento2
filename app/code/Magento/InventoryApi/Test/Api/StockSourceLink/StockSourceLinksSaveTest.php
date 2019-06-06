<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Test\Api\StockSourceLink;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\TestFramework\Assert\AssertArrayContains;
use Magento\TestFramework\TestCase\WebapiAbstract;

class StockSourceLinksSaveTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock-source-links';
    const SERVICE_NAME_SAVE = 'inventoryApiStockSourceLinksSaveV1';
    const SERVICE_NAME_DELETE = 'inventoryApiStockSourceLinksDeleteV1';
    const SERVICE_NAME_GET_LIST = 'inventoryApiGetStockSourceLinksV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     *
     * @see https://app.hiptest.com/projects/69435/test-plan/folders/530616/scenarios/1824134
     */
    public function testExecute()
    {
        $links = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
                StockSourceLinkInterface::STOCK_ID => 10,
                StockSourceLinkInterface::PRIORITY => 1,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
                StockSourceLinkInterface::STOCK_ID => 10,
                StockSourceLinkInterface::PRIORITY => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_SAVE,
                'operation' => self::SERVICE_NAME_SAVE . 'Execute',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['links' => $links]);

        $actualData = $this->getStockSourceLinks();

        self::assertEquals(2, $actualData['total_count']);
        AssertArrayContains::assert($links, $actualData['items']);
    }

    protected function tearDown()
    {
        $links = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
                StockSourceLinkInterface::STOCK_ID => 10,
                StockSourceLinkInterface::PRIORITY => 1,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
                StockSourceLinkInterface::STOCK_ID => 10,
                StockSourceLinkInterface::PRIORITY => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?'
                    . http_build_query(['links' => $links]),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_DELETE,
                'operation' => self::SERVICE_NAME_DELETE . 'Execute',
            ],
        ];

        (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['links' => $links]);

        parent::tearDown();
    }

    /**
     * @return array
     */
    private function getStockSourceLinks(): array
    {
        $requestData = [
            'searchCriteria' => [
                SearchCriteria::FILTER_GROUPS => [
                    [
                        'filters' => [
                            [
                                'field' => StockSourceLinkInterface::STOCK_ID,
                                'value' => 10,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_GET_LIST,
                'operation' => self::SERVICE_NAME_GET_LIST . 'Execute',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
    }
}
