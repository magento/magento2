<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

class ProductOptionRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'bundleProductOptionRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/bundle-products/:sku/options';

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testGet()
    {
        $productSku = 'bundle-product';
        $expected = [
            'required' => true,
            'position' => 0,
            'type' => 'select',
            'title' => 'Bundle Product Items',
            'sku' => $productSku,
            'product_links' => [
                [
                    'sku' => 'simple',
                    'qty' => 1,
                    'position' => 0,
                    'can_change_quantity' => 1,
                    'is_default' => false,
                    'price' => null,
                    'price_type' => null,
                ],
            ],
        ];
        $optionId = $this->getList($productSku)[0]['option_id'];
        $result = $this->get($productSku, $optionId);

        $this->assertArrayHasKey('option_id', $result);
        $expected['product_links'][0]['option_id'] = $result['option_id'];
        unset($result['option_id']);
        $this->assertNotNull($result['product_links'][0]['id']);
        unset($result['product_links'][0]['id']);

        ksort($expected);
        ksort($result);
        ksort($expected['product_links'][0]);
        ksort($result['product_links'][0]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testGetList()
    {
        $productSku = 'bundle-product';
        $expected = [
            [
                'required' => true,
                'position' => 0,
                'type' => 'select',
                'title' => 'Bundle Product Items',
                'sku' => $productSku,
                'product_links' => [
                    [
                        'sku' => 'simple',
                        'qty' => 1,
                        'position' => 0,
                        'can_change_quantity' => 1,
                        'is_default' => false,
                        'price' => null,
                        'price_type' => null,
                    ],
                ],
            ],
        ];
        $result = $this->getList($productSku);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('option_id', $result[0]);
        $expected[0]['product_links'][0]['option_id'] = $result[0]['option_id'];
        unset($result[0]['option_id']);
        $this->assertNotNull($result[0]['product_links'][0]['id']);
        unset($result[0]['product_links'][0]['id']);

        ksort($expected[0]);
        ksort($result[0]);
        ksort($expected[0]['product_links'][0]);
        ksort($result[0]['product_links'][0]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemove()
    {
        $productSku = 'bundle-product';

        $optionId = $this->getList($productSku)[0]['option_id'];
        $result = $this->remove($productSku, $optionId);

        $this->assertTrue($result);

        try {
            $this->get($productSku, $optionId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testAdd()
    {
        $productSku = 'bundle-product';
        $request = [
            'required' => true,
            'position' => 0,
            'type' => 'select',
            'title' => 'test product',
            'product_links' => [],
            'sku' => $productSku,
        ];

        $optionId = $this->add($request);
        $this->assertGreaterThan(0, $optionId);
        $result = $this->get($productSku, $optionId);

        $this->assertArrayHasKey('option_id', $result);
        $this->assertArrayHasKey('sku', $result);
        unset($result['option_id']);

        ksort($result);
        ksort($request);
        $this->assertEquals($request, $result);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testUpdate()
    {
        $productSku = 'bundle-product';
        $request = [
            'title' => 'someTitle',
            'type' => 'select',
            'required' => 1,
            'sku' => $productSku,
        ];

        $options = $this->getList($productSku);

        $this->assertGreaterThan(0, count($options));
        $this->assertArrayHasKey('option_id', $options[0]);

        $optionId = $options[0]['option_id'];
        $this->update($optionId, $request);

        $result = $this->get($productSku, $optionId);

        $this->assertCount(7, $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals($request['title'], $result['title']);
    }

    /**
     * @param int $optionId
     * @param array $option
     * @return string
     */
    protected function update($optionId, $option)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/bundle-products/options/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'bundleProductOptionManagementV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'bundleProductOptionManagementV1Save',
            ],
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $option['optionId'] = $optionId;
        }
        return $this->_webApiCall($serviceInfo, ['option' => $option]);
    }

    /**
     * @param array $option
     * @return string
     */
    protected function add($option)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/bundle-products/options/add',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'bundleProductOptionManagementV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'bundleProductOptionManagementV1Save',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['option' => $option]);
    }

    /**
     * @param string $productSku
     * @param int $optionId
     * @return string
     */
    protected function remove($productSku, $optionId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(':sku', $productSku, self::RESOURCE_PATH) . '/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'optionId' => $optionId]);
    }

    /**
     * @param string $productSku
     * @return string
     */
    protected function getList($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(':sku', $productSku, self::RESOURCE_PATH) . '/all',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
    }

    /**
     * @param string $productSku
     * @param int $optionId
     * @return string
     */
    protected function get($productSku, $optionId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace(':sku', $productSku, self::RESOURCE_PATH) . '/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'optionId' => $optionId]);
    }
}
