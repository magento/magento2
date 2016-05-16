<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    /**
     * Get Product
     *
     * @param $sku
     * @return ProductInterface
     */
    protected function getProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku]);
        return $response;
    }

    /**
     * Update Product
     *
     * @param $product
     * @return mixed
     */
    protected function updateProduct($product)
    {
        $sku = $product[ProductInterface::SKU];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $product[ProductInterface::SKU] = null;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response =  $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Save Product
     *
     * @param $product
     * @return mixed
     */
    protected function saveProduct($product)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Delete Product
     *
     * @param string $sku
     * @return boolean
     */
    protected function deleteProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $sku]) : $this->_webApiCall($serviceInfo);
    }

    public function testProductLinks()
    {
        // Create simple product
        $productData =  [
            ProductInterface::SKU => "product_simple_500",
            ProductInterface::NAME => "Product Simple 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 100,
            ProductInterface::STATUS => 1,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            ProductInterface::EXTENSION_ATTRIBUTES_KEY => [
                'stock_item' => $this->getStockItemData()
            ]
        ];

        $this->saveProduct($productData);

        // Create a group product
        $productLinkData = ["sku" => "group_product_500", "link_type" => "associated",
                            "linked_product_sku" => "product_simple_500", "linked_product_type" => "simple",
                            "position" => 0, "extension_attributes" => ["qty" => 1]];
        $productWithGroupData =  [
            ProductInterface::SKU => "group_product_500",
            ProductInterface::NAME => "Group Product 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'grouped',
            ProductInterface::PRICE => 300,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => [$productLinkData]
        ];

        $this->saveProduct($productWithGroupData);
        $response = $this->getProduct("group_product_500");
        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals(1, count($links));
        $this->assertEquals($productLinkData, $links[0]);

        // update link information for Group Product
        $productLinkData1 = ["sku" => "group_product_500", "link_type" => "associated",
                            "linked_product_sku" => "product_simple_500", "linked_product_type" => "simple",
                            "position" => 0, "extension_attributes" => ["qty" => 4]];
        $productLinkData2 = ["sku" => "group_product_500", "link_type" => "upsell",
                             "linked_product_sku" => "product_simple_500", "linked_product_type" => "simple",
                             "position" => 0, "extension_attributes" => []];
        $productWithGroupData =  [
            ProductInterface::SKU => "group_product_500",
            ProductInterface::NAME => "Group Product 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'grouped',
            ProductInterface::PRICE => 300,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => [$productLinkData1, $productLinkData2]
        ];

        $this->saveProduct($productWithGroupData);
        $response = $this->getProduct("group_product_500");

        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals(2, count($links));
        $this->assertEquals($productLinkData1, $links[1]);
        $this->assertEquals($productLinkData2, $links[0]);

        // Remove link
        $productWithNoLinkData =  [
            ProductInterface::SKU => "group_product_500",
            ProductInterface::NAME => "Group Product 500",
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'grouped',
            ProductInterface::PRICE => 300,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            "product_links" => []
        ];

        $this->saveProduct($productWithNoLinkData);
        $response = $this->getProduct("group_product_500");
        $this->assertArrayHasKey('product_links', $response);
        $links = $response['product_links'];
        $this->assertEquals([], $links);

        $this->deleteProduct("product_simple_500");
        $this->deleteProduct("group_product_500");
    }

    /**
     * @return array
     */
    private function getStockItemData()
    {
        return [
            StockItemInterface::IS_IN_STOCK => 1,
            StockItemInterface::QTY => 100500,
            StockItemInterface::IS_QTY_DECIMAL => 1,
            StockItemInterface::SHOW_DEFAULT_NOTIFICATION_MESSAGE => 0,
            StockItemInterface::USE_CONFIG_MIN_QTY => 0,
            StockItemInterface::USE_CONFIG_MIN_SALE_QTY => 0,
            StockItemInterface::MIN_QTY => 1,
            StockItemInterface::MIN_SALE_QTY => 1,
            StockItemInterface::MAX_SALE_QTY => 100,
            StockItemInterface::USE_CONFIG_MAX_SALE_QTY => 0,
            StockItemInterface::USE_CONFIG_BACKORDERS => 0,
            StockItemInterface::BACKORDERS => 0,
            StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY => 0,
            StockItemInterface::NOTIFY_STOCK_QTY => 0,
            StockItemInterface::USE_CONFIG_QTY_INCREMENTS => 0,
            StockItemInterface::QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_ENABLE_QTY_INC => 0,
            StockItemInterface::ENABLE_QTY_INCREMENTS => 0,
            StockItemInterface::USE_CONFIG_MANAGE_STOCK => 1,
            StockItemInterface::MANAGE_STOCK => 1,
            StockItemInterface::LOW_STOCK_DATE => null,
            StockItemInterface::IS_DECIMAL_DIVIDED => 0,
            StockItemInterface::STOCK_STATUS_CHANGED_AUTO => 0,
        ];
    }
}
