<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductRenderListInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRenderListV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products-render-info/';

    /**
     * Test get list of products render information: prices, buttons data, etc...
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_special_price.php
     * @dataProvider productRenderInfoProvider
     * @param array $expectedRenderInfo
     * @param string $ids
     * @return void
     */
    public function testGetList(array $expectedRenderInfo)
    {
        $expectedIds = [21, 31];
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        $filter1 = $filterBuilder
            ->setField('entity_id')
            ->setValue(implode(",", $expectedIds))
            ->setConditionType('in')
            ->create();

        $searchCriteriaBuilder->addFilters([$filter1]);

        $searchData['search_criteria'] = $searchCriteriaBuilder->create()->__toArray();
        $searchData['store_id'] = 1;
        $searchData['currency_code'] = 'USD';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "?" . http_build_query($searchData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $searchData);

        foreach ($result['items'] as $id => $resultItem) {
            $expectedItem = $expectedRenderInfo[$id];
            $this->assertEquals($expectedItem['name'], $resultItem['name']);
            $this->assertEquals($expectedItem['type'], $resultItem['type']);
            $this->assertEquals($expectedItem['is_salable'], $resultItem['is_salable']);
            $this->assertEquals($expectedItem['store_id'], $resultItem['store_id']);
            $this->assertEquals($expectedItem['currency_code'], $resultItem['currency_code']);
            $this->assertEquals(
                $expectedItem['final_price'],
                $resultItem['price_info']['formatted_prices']['final_price']
            );
        }
    }

    /**
     * Provider for parts of product information
     * @return array
     */
    public function productRenderInfoProvider()
    {
        return [
            'simple_products_variation' => [
                [
                    [
                        'id' => 21,
                        'name' => 'Virtual Product',
                        'type' => 'virtual',
                        'is_salable' => true,
                        'store_id' => 1,
                        'currency_code' => 'USD',
                        'final_price' => '<span class="price">$10.00</span>'
                    ],
                    [
                        'id' => 31,
                        'name' => 'Simple Product',
                        'type' => 'simple',
                        'is_salable' => true,
                        'store_id' => 1,
                        'currency_code' => 'USD',
                        'final_price' => '<span class="price">$5.99</span>'
                    ]
                ]
            ]
        ];
    }
}
