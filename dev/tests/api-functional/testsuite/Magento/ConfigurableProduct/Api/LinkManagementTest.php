<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\Webapi\Model\Rest\Config;

class LinkManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'configurableProductLinkManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/configurable-products';

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetChildren()
    {
        $productSku = 'configurable';

        /** @var array $result */
        $result = $this->getChildren($productSku);
        $this->assertCount(2, $result);

        foreach ($result as $product) {
            $this->assertArrayHasKey('custom_attributes', $product);
            $this->assertArrayHasKey('price', $product);
            $this->assertArrayHasKey('updated_at', $product);

            $this->assertArrayHasKey('name', $product);
            $this->assertContains('Configurable Option', $product['name']);

            $this->assertArrayHasKey('sku', $product);
            $this->assertContains('simple_', $product['sku']);

            $this->assertArrayHasKey('status', $product);
            $this->assertEquals('1', $product['status']);

            $this->assertArrayHasKey('visibility', $product);
            $this->assertEquals('1', $product['visibility']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/delete_association.php
     */
    public function testAddChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/child',
                'httpMethod' => Config::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'AddChild'
            ]
        ];
        $res = $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'childSku' => $childSku]);
        $this->assertTrue($res);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testRemoveChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';
        $this->assertTrue($this->removeChild($productSku, $childSku));
    }

    protected function removeChild($productSku, $childSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/%s/child/%s';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf($resourcePath, $productSku, $childSku),
                'httpMethod' => Config::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'RemoveChild'
            ]
        ];
        $requestData = ['productSku' => $productSku, 'childSku' => $childSku];
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
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku  . '/children',
                'httpMethod' => Config::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetChildren'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
    }
}
