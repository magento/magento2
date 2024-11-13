<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductLinkManagementTest extends WebapiAbstract
{
    const SERVICE_NAME = 'bundleProductLinkManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/bundle-products';

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testGetChildren()
    {
        $productSku = 'bundle-product';
        $expected = [
            [
                'sku' => 'simple',
                'position' => 0,
                'qty' => 1,
            ],
        ];

        $result = $this->getChildren($productSku);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('option_id', $result[0]);
        $this->assertArrayHasKey('is_default', $result[0]);
        $this->assertArrayHasKey('can_change_quantity', $result[0]);
        $this->assertArrayHasKey('price', $result[0]);
        $this->assertArrayHasKey('price_type', $result[0]);
        $this->assertNotNull($result[0]['id']);

        unset($result[0]['option_id'], $result[0]['is_default'], $result[0]['can_change_quantity']);
        unset($result[0]['price'], $result[0]['price_type'], $result[0]['id']);

        ksort($result[0]);
        ksort($expected[0]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testRemoveChild()
    {
        $productSku = 'bundle-product';
        $childSku = 'simple';
        $optionIds = $this->getProductOptions(3);
        $optionId = array_shift($optionIds);
        $this->assertTrue($this->removeChild($productSku, $optionId, $childSku));
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     */
    public function testAddChild()
    {
        $productSku = 'bundle-product';
        $children = $this->getChildren($productSku);

        $optionId = $children[0]['option_id'];

        $linkedProduct = [
            'sku' => 'virtual-product',
            'option_id' => $optionId,
            'position' => '1',
            'is_default' => 1,
            'priceType' => 2,
            'price' => 151.34,
            'qty' => 8,
            'can_change_quantity' => 1,
        ];

        $childId = $this->addChild($productSku, $optionId, $linkedProduct);
        $this->assertGreaterThan(0, $childId);
    }

    /**
     * Verify empty out of stock bundle product is in stock after child has been added.
     *
     * @magentoApiDataFixture Magento/Bundle/_files/empty_bundle_product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testBundleProductIsInStockAfterAddChild(): void
    {
        $productSku = 'bundle-product';
        $option = [
            'required' => 1,
            'position' => 0,
            'type' => 'select',
            'title' => 'option 1',
            'sku' => $productSku,
        ];
        self::assertFalse($this->isProductInStock($productSku));
        $optionId = $this->addOption($option);
        $linkedProduct = [
            'sku' => 'virtual-product',
            'option_id' => $optionId,
            'position' => '1',
            'is_default' => 1,
            'priceType' => 2,
            'price' => 151.34,
            'qty' => 8,
            'can_change_quantity' => 1,
        ];

        $this->addChild($productSku, $optionId, $linkedProduct);
        self::assertTrue($this->isProductInStock($productSku));
    }

    /**
     * Verify in stock bundle product is out stock after child has been removed.
     *
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     *
     * @return void
     */
    public function testBundleProductIsOutOfStockAfterRemoveChild(): void
    {
        $productSku = 'bundle-product';
        $childSku = 'simple';
        $optionIds = $this->getProductOptions(3);
        $optionId = array_shift($optionIds);
        self::assertTrue($this->isProductInStock($productSku));
        $this->removeChild($productSku, $optionId, $childSku);
        self::assertFalse($this->isProductInStock($productSku));
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     */
    public function testSaveChild()
    {
        $productSku = 'bundle-product';
        $children = $this->getChildren($productSku);

        $linkedProduct = $children[0];

        //Modify a few fields
        $linkedProduct['is_default'] = true;
        $linkedProduct['qty'] = 2;

        $this->assertTrue($this->saveChild($productSku, $linkedProduct));
        $children = $this->getChildren($productSku);
        $this->assertEquals($linkedProduct, $children[0]);
    }

    /**
     * @param string $productSku
     * @param array $linkedProduct
     * @return string
     */
    private function saveChild($productSku, $linkedProduct)
    {
        $resourcePath = self::RESOURCE_PATH . '/:sku/links/:id';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(
                    [':sku', ':id'],
                    [$productSku, $linkedProduct['id']],
                    $resourcePath
                ),
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'SaveChild',
            ],
        ];
        return $this->_webApiCall(
            $serviceInfo,
            ['sku' => $productSku, 'linkedProduct' => $linkedProduct]
        );
    }

    /**
     * @param string $productSku
     * @param int $optionId
     * @param array $linkedProduct
     * @return string
     */
    private function addChild($productSku, $optionId, $linkedProduct)
    {
        $resourcePath = self::RESOURCE_PATH . '/:sku/links/:optionId';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(
                    [':sku', ':optionId'],
                    [$productSku, $optionId],
                    $resourcePath
                ),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'AddChildByProductSku',
            ],
        ];
        return $this->_webApiCall(
            $serviceInfo,
            ['sku' => $productSku, 'optionId' => $optionId, 'linkedProduct' => $linkedProduct]
        );
    }

    protected function getProductOptions($productId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\Product::class);
        $product->load($productId);
        /** @var  \Magento\Bundle\Model\Product\Type $type */
        $type = Bootstrap::getObjectManager()->get(\Magento\Bundle\Model\Product\Type::class);
        return $type->getOptionsIds($product);
    }

    protected function removeChild($productSku, $optionId, $childSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/%s/options/%s/children/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $optionId, $childSku),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'removeChild',
            ],
        ];
        $requestData = ['sku' => $productSku, 'optionId' => $optionId, 'childSku' => $childSku];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @param string $productSku
     * @return string
     */
    protected function getChildren($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/children',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getChildren',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
    }

    /**
     * Check product stock status.
     *
     * @param string $productSku
     * @return bool
     */
    private function isProductInStock(string $productSku): bool
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/stockStatuses/' . $productSku,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogInventoryStockRegistryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'catalogInventoryStockRegistryV1getStockStatusBySku',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);

        return (bool)$result['stock_status'];
    }

    /**
     * Add option to bundle product.
     *
     * @param array $option
     * @return int
     */
    private function addOption(array $option): int
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/bundle-products/options/add',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'bundleProductOptionManagementV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'bundleProductOptionManagementV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['option' => $option]);
    }
}
