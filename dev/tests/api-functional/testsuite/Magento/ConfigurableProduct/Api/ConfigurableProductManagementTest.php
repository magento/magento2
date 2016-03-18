<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
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
                        ]
                    ]
                ]
            ]

        ];
        $actual = $this->_webApiCall($serviceInfo, $data);

        $expectedItems = [
            [
                'sku' => 'test-',
                'price' => 10.0,
                'name' => '-',
                'status' => 1,
                'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                'options' => [],
                'product_links' => [],
                'custom_attributes' => [
                    [
                        'attribute_code' => 'test_configurable',
                        'value' => $attributeOptionValue
                    ]
                ],
                'tier_prices' => []
            ]
        ];
        ksort($expectedItems);
        ksort($actual);
        $this->assertEquals($expectedItems, $actual);
    }
}
