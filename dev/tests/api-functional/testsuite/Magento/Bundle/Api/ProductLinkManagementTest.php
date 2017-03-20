<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

use Magento\TestFramework\Helper\Bootstrap;

class ProductLinkManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getChildren',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
    }
}
