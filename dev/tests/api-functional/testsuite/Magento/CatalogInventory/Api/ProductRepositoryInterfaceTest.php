<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    const KEY_EXTENSION_ATTRIBUTES = ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY;
    const KEY_STOCK_ITEM = StockStatusInterface::STOCK_ITEM;
    const KEY_QTY = StockStatusInterface::QTY;
    const KEY_ITEM_ID = 'item_id';
    const KEY_PRODUCT_ID = StockStatusInterface::PRODUCT_ID;
    const KEY_CUSTOM_ATTRIBUTES = 'custom_attributes';
    const KEY_ATTRIBUTE_CODE = \Magento\Eav\Api\Data\AttributeInterface::ATTRIBUTE_CODE;
    const CODE_QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';

    const PRODUCT_SKU = 'sku-test-catalog-inventory';

    /**
     * Tests the 'happy path'
     */
    public function testCatalogInventory()
    {
        // create a simple product with catalog inventory
        $qty = 1234;
        $productData = $this->getSimpleProductData($qty);
        $stockItemData = $this->getStockItemData($qty);
        $this->assertArrayNotHasKey(self::KEY_ITEM_ID, $stockItemData);
        $this->assertArrayNotHasKey(self::KEY_PRODUCT_ID, $stockItemData);
        $productData[self::KEY_EXTENSION_ATTRIBUTES] = $stockItemData;

        $response = $this->saveProduct($productData);

        $this->assertArrayHasKey(self::KEY_EXTENSION_ATTRIBUTES, $response);
        $this->assertTrue(isset($response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM]));
        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'CREATE: Expected qty to be same: ' . $qty .', '. $returnedQty);
        $this->assertArrayHasKey(self::KEY_ITEM_ID, $stockItemData);
        $this->assertArrayHasKey(self::KEY_PRODUCT_ID, $stockItemData);

        // officially get the product
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'GET: Expected qty to be same: ' . $qty .', '. $returnedQty);

        // update the catalog inventory
        $qty = $this->getDifferent($qty);  // update the quantity
        $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM][self::KEY_QTY] = $qty;

        $response = $this->updateProduct($response);

        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'UPDATE 1: Expected qty to be same: ' . $qty .', '. $returnedQty);

        // update the product without any mention of catalog inventory; no change expected for catalog inventory
        // note: $qty expected to be the same as previously set, above
        $newPrice = $this->getDifferent($response[ProductInterface::PRICE]);
        $response[ProductInterface::PRICE] = $newPrice;
        unset($response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM]);

        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_EXTENSION_ATTRIBUTES, $response);
        $this->assertTrue(isset($response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM]));
        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'UPDATE 2: Expected qty to be same: ' . $qty .', '. $returnedQty);
        $this->assertEquals($newPrice, $response[ProductInterface::PRICE]);

        // delete the product; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }

    /**
     * Tests conditions that stray from the 'happy path'
     */
    public function testCatalogInventoryWithBogusData()
    {
        // create a simple product with catalog inventory
        $qty = 666;
        $productData = $this->getSimpleProductData($qty);
        $stockItemData = $this->getStockItemData($qty);
        $this->assertArrayNotHasKey(self::KEY_ITEM_ID, $stockItemData);
        $this->assertArrayNotHasKey(self::KEY_PRODUCT_ID, $stockItemData);
        $productData[self::KEY_EXTENSION_ATTRIBUTES] = $stockItemData;

        $response = $this->saveProduct($productData);

        $this->assertArrayHasKey(self::KEY_EXTENSION_ATTRIBUTES, $response);
        $this->assertTrue(isset($response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM]));
        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'POST 1: Expected qty to be same: ' . $qty .', '. $returnedQty);
        $this->assertArrayHasKey(self::KEY_ITEM_ID, $stockItemData);
        $this->assertArrayHasKey(self::KEY_PRODUCT_ID, $stockItemData);

        // re-save the catalog inventory:
        // -- update quantity (which should be honored)
        // -- supply an incorrect product id (which should be ignored, and be replaced with the actual one)
        // -- supply an incorrect website id (which should be ignored, and be replaced with the actual one)
        $qty = 777;  // update the quantity
        $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM][self::KEY_QTY] = $qty;

        $originalProductId = $stockItemData[self::KEY_PRODUCT_ID];
        $bogusProductId = $this->getDifferent($originalProductId);
        $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM][self::KEY_PRODUCT_ID] = $bogusProductId;

        $response = $this->saveProduct($response);

        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $returnedQty = $stockItemData[self::KEY_QTY];
        $this->assertEquals($qty, $returnedQty, 'POST 2: Expected qty to be same: ' . $qty .', '. $returnedQty);

        $returnedProductId = $stockItemData[self::KEY_PRODUCT_ID];
        $this->assertEquals($originalProductId, $returnedProductId);

        // delete the product; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }

    /**
     * Tests that creating a simple product has a side-effect of creating catalog inventory
     */
    public function testSimpleProductCreationWithoutSpecifyingCatalogInventory()
    {
        // create a simple product with catalog inventory
        $qty = null;
        $productData = $this->getSimpleProductData($qty);
        $this->assertArrayNotHasKey(self::KEY_CUSTOM_ATTRIBUTES, $productData);
        $this->assertArrayNotHasKey(self::KEY_EXTENSION_ATTRIBUTES, $productData);

        $response = $this->saveProduct($productData);

        $this->assertArrayHasKey(self::KEY_EXTENSION_ATTRIBUTES, $response);
        $this->assertTrue(isset($response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM]));
        $stockItemData = $response[self::KEY_EXTENSION_ATTRIBUTES][self::KEY_STOCK_ITEM];
        $this->assertArrayHasKey(self::KEY_ITEM_ID, $stockItemData);
        $this->assertArrayHasKey(self::KEY_PRODUCT_ID, $stockItemData);

        // delete the product; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }

    // --- my helpers -----------------------------------------------------------------------------

    /**
     * Return a value that is different than the original one
     *
     * @param int $original
     * @return int
     */
    protected function getDifferent($original)
    {
        return 1 + $original * $original;
    }

    /**
     * Get Simple Product Data
     *
     * @param int $qty
     * @return array
     */
    protected function getSimpleProductData($qty = 1000)
    {
        $productData = [
            ProductInterface::SKU => self::PRODUCT_SKU,
            ProductInterface::NAME => self::PRODUCT_SKU,
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 10,
            ProductInterface::STATUS => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
        ];

        if ($qty != null) {
            $productData[self::KEY_CUSTOM_ATTRIBUTES] = [
                [self::KEY_ATTRIBUTE_CODE => 'description', 'value' => 'My Product Description'],
                [self::KEY_ATTRIBUTE_CODE => self::CODE_QUANTITY_AND_STOCK_STATUS, 'value' => [true, $qty]],
            ];
        }

        return $productData;
    }

    /**
     * Get sample Stock Item data
     *
     * @param int $qty
     * @return array
     */
    protected function getStockItemData($qty = 1000)
    {
        return [
            self::KEY_STOCK_ITEM => [
                self::KEY_QTY => $qty,
                'is_in_stock' => true,
                'is_qty_decimal' => false,
                'show_default_notification_message' => false,
                'use_config_min_qty' => true,
                'min_qty' => 0,
                'use_config_min_sale_qty' => 1,
                'min_sale_qty' => 1,
                'use_config_max_sale_qty' => true,
                'max_sale_qty' => 10000,
                'use_config_backorders' => true,
                'backorders' => 0,
                'use_config_notify_stock_qty' => true,
                'notify_stock_qty' => 1,
                'use_config_qty_increments' => true,
                'qty_increments' => 0,
                'use_config_enable_qty_inc' => false,
                'enable_qty_increments' => false,
                'use_config_manage_stock' => false,
                'manage_stock' => true,
                'low_stock_date' => "0",
                'is_decimal_divided' => false,
                'stock_status_changed_auto' => 0,
            ]
        ];
    }

    // --- common REST helpers --------------------------------------------------------------------

    /**
     * Get a product via its sku
     *
     * @param string $sku
     * @return array the product data
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
     * Save a product
     *
     * @param array $product
     * @return array the created product data
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
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Update an existing product via its sku
     *
     * @param array $product
     * @return array the product data, including any updates
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
     * Delete a product via its sku
     *
     * @param string $sku
     * @return bool
     */
    protected function deleteProduct($sku)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $sku;
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
        $requestData = ['sku' => $sku];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }
}
