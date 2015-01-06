<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\Webapi\Model\Rest\Config as RestConfig;
use Magento\TestFramework\Helper\Bootstrap;

class ConfigurableProductManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'configurableProductConfigurableProductManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/configurable-products/variation';

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     */
    public function testGetVariation()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GenerateVariation'
            ]
        ];
        /** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository */
        $attributeRepository = Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Api\ProductAttributeRepositoryInterface'
        );
        $attribute = $attributeRepository->get('test_configurable');
        $attributeOptionValue = $attribute->getOptions()[0]->getValue();
        $data = [
            'product' => [
                'sku' => 'test',
                'price' => 10.0
            ],
            'options' => [
                [
                    'attribute_id' => 'test_configurable',
                    'values' => [
                        [
                            'value_index' => $attributeOptionValue,
                            'pricing_value' => 100.0
                        ]
                    ]
                ]
            ]

        ];
        $actual = $this->_webApiCall($serviceInfo, $data);

        $expectedItems = [
            [
                'sku' => 'test-',
                'price' => 110.0,
                'name' => '-',
                'store_id' => 1,
                'status' => 1,
                'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                'custom_attributes' => [
                    [
                        'attribute_code' => 'test_configurable',
                        'value' => $attributeOptionValue
                    ]
                ]
            ]
        ];
        ksort($expectedItems);
        ksort($actual);
        $this->assertEquals($expectedItems, $actual);
    }
}
