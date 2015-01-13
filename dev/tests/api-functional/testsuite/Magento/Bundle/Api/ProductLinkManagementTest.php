<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Webapi\Model\Rest\Config;

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
        $this->assertArrayHasKey('is_defined', $result[0]);
        $this->assertArrayHasKey('price', $result[0]);
        $this->assertArrayHasKey('price_type', $result[0]);

        unset($result[0]['option_id'], $result[0]['is_default'], $result[0]['is_defined']);
        unset($result[0]['price'], $result[0]['price_type']);

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
     * @param string $productSku
     * @param int $optionId
     * @param array $linkedProduct
     * @return string
     */
    private function addChild($productSku, $optionId, $linkedProduct)
    {
        $resourcePath = self::RESOURCE_PATH . '/:productSku/links/:optionId';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(
                    [':productSku', ':optionId'],
                    [$productSku, $optionId],
                    $resourcePath
                ),
                'httpMethod' => Config::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'AddChildByProductSku',
            ],
        ];
        return $this->_webApiCall(
            $serviceInfo,
            ['productSku' => $productSku, 'optionId' => $optionId, 'linkedProduct' => $linkedProduct]
        );
    }

    protected function getProductOptions($productId)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\Product');
        $product->load($productId);
        /** @var  \Magento\Bundle\Model\Product\Type $type */
        $type = Bootstrap::getObjectManager()->get('Magento\Bundle\Model\Product\Type');
        return $type->getOptionsIds($product);
    }

    protected function removeChild($productSku, $optionId, $childSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/%s/option/%s/child/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $optionId, $childSku),
                'httpMethod' => Config::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'removeChild',
            ],
        ];
        $requestData = ['productSku' => $productSku, 'optionId' => $optionId, 'childSku' => $childSku];
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
                'httpMethod' => Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getChildren',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['productId' => $productSku]);
    }
}
