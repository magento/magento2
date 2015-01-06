<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Class LowStockItemsTest
 */
class LowStockItemsTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/stockItem/lowStock/';

    /**
     * @param float $qty
     * @param int $currentPage
     * @param int $pageSize
     * @param array $result
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @dataProvider getLowStockItemsDataProvider
     */
    public function testGetLowStockItems($qty, $currentPage, $pageSize, $result)
    {
        $requestData = ['websiteId' => 1, 'qty' => $qty, 'pageSize' => $pageSize, 'currentPage' => $currentPage];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogInventoryStockRegistryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogInventoryStockRegistryV1GetLowStockItems',
            ],
        ];
        $output = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertArrayHasKey('items', $output);
    }

    /**
     * @return array
     */
    public function getLowStockItemsDataProvider()
    {
        return [
            [
                100,
                1,
                10,
                [
                    'search_criteria' => ['current_page' => 1, 'page_size' => 10, 'qty' => 100],
                    'total_count' => 2,
                    'items' => [
                        [
                            'product_id' => 10,
                            'website_id' => 1,
                            'stock_id' => 1,
                            'qty' => 100,
                            'stock_status' => null,
                            'stock_item' => null,
                        ],
                        [
                            'product_id' => 12,
                            'website_id' => 1,
                            'stock_id' => 1,
                            'qty' => 140,
                            'stock_status' => null,
                            'stock_item' => null
                        ],
                    ]
                ],
            ],
        ];
    }
}
