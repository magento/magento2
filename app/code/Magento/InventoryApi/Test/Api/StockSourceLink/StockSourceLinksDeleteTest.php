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

class StockSourceLinksDeleteTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock-source-links';
    const RESOURCE_DELETE_PATH = '/V1/inventory/stock-source-links-delete';

    const SERVICE_NAME_SAVE = 'inventoryApiStockSourceLinksSaveV1';
    const SERVICE_NAME_DELETE = 'inventoryApiStockSourceLinksDeleteV1';
    const SERVICE_NAME_GET_LIST = 'inventoryApiGetStockSourceLinksV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     */
    public function testExecute()
    {
        $linksForDelete = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
        ];
        $expectedLinksAfterDeleting = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-3',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-disabled',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_DELETE_PATH . '?'
                    . http_build_query(['links' => $linksForDelete]),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME_DELETE,
                'operation' => self::SERVICE_NAME_DELETE . 'Execute',
            ],
        ];
        (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, ['links' => $linksForDelete]);

        $actualData = $this->getStockSourceLinks();

        self::assertEquals(2, $actualData['total_count']);
        AssertArrayContains::assert($expectedLinksAfterDeleting, $actualData['items']);
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
