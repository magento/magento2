<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\Webapi\Model\Rest\Config;

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

        $expectedValues = [
            ['pricing_value' => 5, 'is_percent' => 0],
            ['pricing_value' => 5, 'is_percent' => 0]
        ];

        $this->assertCount(count($expectedValues), $option['values']);

        foreach ($option['values'] as $key => $value) {
            $this->assertTrue(is_array($value));
            $this->assertNotEmpty($value);

            $this->assertArrayHasKey($key, $expectedValues);
            $expectedValue = $expectedValues[$key];

            $this->assertArrayHasKey('pricing_value', $value);
            $this->assertEquals($expectedValue['pricing_value'], $value['pricing_value']);

            $this->assertArrayHasKey('is_percent', $value);
            $this->assertEquals($expectedValue['is_percent'], $value['is_percent']);
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
     * @expectedException \Exception
     * @expectedExceptionMessage Requested option doesn't exist: -42
     */
    public function testGetUndefinedOption()
    {
        $productSku = 'configurable';
        $attributeId = -42;
        $this->get($productSku, $attributeId);
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
                'httpMethod' => Config::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save'
            ]
        ];
        $option = [
            'attribute_id' => 'test_configurable',
            'type' => 'select',
            'label' => 'Test',
            'values' => [
                [
                    'value_index' => 1,
                    'pricing_value' => '3',
                    'is_percent' => 0
                ]
            ],
        ];
        /** @var int $result */
        $result = $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'option' => $option]);
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
                'httpMethod' => Config::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save'
            ]
        ];

        $option = [
            'label' => 'Update Test Configurable'
        ];

        $requestBody = ['option' => $option];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $requestBody['productSku'] = $productSku;
            $requestBody['option']['id'] = $optionId;
        }

        $result = $this->_webApiCall($serviceInfo, $requestBody);
        $this->assertGreaterThan(0, $result);
        $configurableAttribute = $this->getConfigurableAttribute($productSku);
        $this->assertEquals($option['label'], $configurableAttribute[0]['label']);
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
                'httpMethod' => Config::HTTP_METHOD_GET
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
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
                'httpMethod' => Config::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'optionId' => $optionId]);
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
                'httpMethod'   => Config::HTTP_METHOD_GET
            ],
            'soap' => [
                'service'        => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation'      => self::SERVICE_NAME . 'Get'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku, 'optionId' => $optionId]);
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
                'httpMethod'   => Config::HTTP_METHOD_GET
            ],
            'soap' => [
                'service'        => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation'      => self::SERVICE_NAME . 'GetList'
            ]
        ];
        return $this->_webApiCall($serviceInfo, ['productSku' => $productSku]);
    }

}
