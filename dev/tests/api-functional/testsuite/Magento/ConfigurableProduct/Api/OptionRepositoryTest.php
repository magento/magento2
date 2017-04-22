<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Api;

class OptionRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'configurableProductOptionRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/configurable-products';

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGet()
    {
        $productSku = 'configurable';

        $options = $this->getList($productSku);
        $this->assertTrue(is_array($options));
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            /** @var array $result */
            $result = $this->get($productSku, $option['id']);

            $this->assertTrue(is_array($result));
            $this->assertNotEmpty($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertEquals($option['id'], $result['id']);

            $this->assertArrayHasKey('attribute_id', $result);
            $this->assertEquals($option['attribute_id'], $result['attribute_id']);

            $this->assertArrayHasKey('label', $result);
            $this->assertEquals($option['label'], $result['label']);

            $this->assertArrayHasKey('values', $result);
            $this->assertTrue(is_array($result['values']));
            $this->assertEquals($option['values'], $result['values']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetList()
    {
        $productSku = 'configurable';

        /** @var array $result */
        $result = $this->getList($productSku);

        $this->assertNotEmpty($result);
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey(0, $result);

        $option = $result[0];

        $this->assertNotEmpty($option);
        $this->assertTrue(is_array($option));

        $this->assertArrayHasKey('id', $option);
        $this->assertArrayHasKey('label', $option);
        $this->assertEquals($option['label'], 'Test Configurable');

        $this->assertArrayHasKey('values', $option);
        $this->assertTrue(is_array($option));
        $this->assertNotEmpty($option);

        $this->assertCount(2, $option['values']);

        foreach ($option['values'] as $value) {
            $this->assertTrue(is_array($value));
            $this->assertNotEmpty($value);

            $this->assertArrayHasKey('value_index', $value);
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testGetUndefinedProduct()
    {
        $productSku = 'product_not_exist';
        $this->getList($productSku);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testGetUndefinedOption()
    {
        $expectedMessage = 'Requested option doesn\'t exist: %1';
        $productSku = 'configurable';
        $attributeId = -42;
        try {
            $this->get($productSku, $attributeId);
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                'SoapFault does not contain expected message.'
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$attributeId], $errorObj['parameters']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testDelete()
    {
        $productSku = 'configurable';

        $optionList = $this->getList($productSku);
        $optionId = $optionList[0]['id'];
        $resultRemove = $this->delete($productSku, $optionId);
        $optionListRemoved = $this->getList($productSku);

        $this->assertTrue($resultRemove);
        $this->assertEquals(count($optionList) - 1, count($optionListRemoved));
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     */
    public function testAdd()
    {
        $productSku = 'simple';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save'
            ]
        ];
        $option = [
            'attribute_id' => 'test_configurable',
            'label' => 'Test',
            'values' => [
                [
                    'value_index' => 1,
                ]
            ],
        ];
        /** @var int $result */
        $result = $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'option' => $option]);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     */
    public function testUpdate()
    {
        $productSku = 'configurable';
        $configurableAttribute = $this->getConfigurableAttribute($productSku);
        $optionId = $configurableAttribute[0]['id'];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options' . '/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save'
            ]
        ];

        $requestBody = [
            'option' => [
                'label' => 'Update Test Configurable',
            ]
        ];

        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $requestBody['sku'] = $productSku;
            $requestBody['option']['id'] = $optionId;
        }

        $result = $this->_webApiCall($serviceInfo, $requestBody);
        $this->assertGreaterThan(0, $result);
        $configurableAttribute = $this->getConfigurableAttribute($productSku);
        $this->assertEquals($requestBody['option']['label'], $configurableAttribute[0]['label']);
    }

    /**
     * @param string $productSku
     * @return array
     */
    protected function getConfigurableAttribute($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options/all',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
    }

    /**
     * @param string $productSku
     * @param int $optionId
     * @return bool
     */
    private function delete($productSku, $optionId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options/' . $optionId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'id' => $optionId]);
    }

    /**
     * @param $productSku
     * @param $optionId
     * @return array
     */
    protected function get($productSku, $optionId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options/' . $optionId,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service'        => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation'      => self::SERVICE_NAME . 'Get'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku, 'id' => $optionId]);
    }

    /**
     * @param $productSku
     * @return array
     */
    protected function getList($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku . '/options/all',
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET
            ],
            'soap' => [
                'service'        => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation'      => self::SERVICE_NAME . 'GetList'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['sku' => $productSku]);
    }
}
