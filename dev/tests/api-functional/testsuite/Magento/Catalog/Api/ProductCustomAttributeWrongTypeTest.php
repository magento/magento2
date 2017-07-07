<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductCustomAttributeWrongTypeTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/';
    const SIMPLE_PRODUCT_SKU = 'simple';

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $this->deleteProductBySku(self::SIMPLE_PRODUCT_SKU);
        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     * @expectedException \Exception
     */
    public function testCustomAttributeWrongType()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'simple',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->setExpectedException('Exception', 'Attribute "meta_title" has invalid value.');
        } else {
            $this->setExpectedException('Exception', 'Attribute \"meta_title\" has invalid value.');
        }

        $this->_webApiCall($serviceInfo, $this->getRequestData());
    }

    protected function getRequestData()
    {
        return [
            "product"=> [

                "sku"=> 'simple',
                "name"=> "SimpleProd",
                "attribute_set_id"=> 4,
                "price"=> 555,
                "status"=> 1,
                "visibility"=> 4,
                "type_id"=> "simple",
                "created_at"=> "2016-03-28 07=>49=>39",
                "updated_at"=> "2016-03-29 06=>45=>30",
                "weight"=> 434,
                "extension_attributes"=> [
                    "stock_item"=> [
                        "item_id"=> 1,
                        "product_id"=> 1,
                        "stock_id"=> 1,
                        "qty"=> 1000,
                        "is_in_stock"=> true,
                        "is_qty_decimal"=> false,
                        "show_default_notification_message"=> false,
                        "use_config_min_qty"=> true,
                        "min_qty"=> 0,
                        "use_config_min_sale_qty"=> 1,
                        "min_sale_qty"=> 1,
                        "use_config_max_sale_qty"=> true,
                        "max_sale_qty"=> 10000,
                        "use_config_backorders"=> true,
                        "backorders"=> 0,
                        "use_config_notify_stock_qty"=> true,
                        "notify_stock_qty"=> 1,
                        "use_config_qty_increments"=> true,
                        "qty_increments"=> 0,
                        "use_config_enable_qty_inc"=> false,
                        "enable_qty_increments"=> false,
                        "use_config_manage_stock"=> true,
                        "manage_stock"=> true,
                        "low_stock_date"=> null,
                        "is_decimal_divided"=> false,
                        "stock_status_changed_auto"=> 0
                    ]
                ],
                "product_links"=> [],
                "options"=> [
                    [
                        "product_sku"=> "simple",
                        "option_id"=> 1,
                        "title"=> "simple",
                        "type"=> "drop_down",
                        "sort_order"=> 1,
                        "is_require"=> true,
                        "max_characters"=> 0,
                        "image_size_x"=> 0,
                        "image_size_y"=> 0,
                        "values"=> [
                            [
                                "title"=> "sadasdad",
                                "sort_order"=> 1,
                                "price"=> 555,
                                "price_type"=> "fixed",
                                "sku"=> "dfsdfsdf",
                                "option_type_id"=> 1
                            ]
                        ]
                    ]
                ],
                "media_gallery_entries"=> [],
                "tier_prices"=> [],
                "custom_attributes"=> [
                    [
                        "attribute_code"=> "meta_title",
                        "value"=> [1,2,3]
                    ],
                ],
            ],
        ];
    }

    protected function deleteProductBySku($productSku)
    {
        $resourcePath = self::RESOURCE_PATH . $productSku;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        $requestData = ["sku" => $productSku];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }
}
