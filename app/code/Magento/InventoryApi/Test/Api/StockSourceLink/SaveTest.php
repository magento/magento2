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

class SaveTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/stock-source-link';
    const SERVICE_NAME = 'inventoryApiStockSourceLinksSaveV1';
    /**#@-*/

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     */
    public function testExecute()
    {
        $links = [
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-1',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
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
            ],
            [
                StockSourceLinkInterface::SOURCE_CODE => 'eu-2',
                StockSourceLinkInterface::STOCK_ID => 10,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?'
                    . http_build_query(['links' => $links]),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Execute',
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
            'searchCriteria' => [SearchCriteria::PAGE_SIZE => 10],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'inventoryApiStockSourceLinksV1',
                'operation' => 'inventoryApiStockSourceLinksV1V1GetList',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST)
            ? $this->_webApiCall($serviceInfo)
            : $this->_webApiCall($serviceInfo, $requestData);
    }
}
