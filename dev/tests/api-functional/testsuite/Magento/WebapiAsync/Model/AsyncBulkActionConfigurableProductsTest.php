<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Eav\Model\AttributeRepository;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Check async request for configurable products creation service
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AsyncBulkActionConfigurableProductsTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'asyncBulkConfigurableProductsV1';
    private const ASYNC_BULK_RESOURCE_PATH = '/async/bulk/V1/configurable-products';

    private const BULK_UUID_KEY = 'bulk_uuid';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->attributeRepository = $this->objectManager->get(\Magento\Eav\Model\AttributeRepository::class);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     *
     * @return void
     */
    public function testAsyncBulkUpdate()
    {
        $productSku = 'configurable';
        $attribute = $this->attributeRepository->get('catalog_product', 'test_configurable');

        $this->_markTestAsRestOnly();
        $requestData = [
            [
                'sku' => $productSku,
                'option' => [
                    'attribute_id' => (int)$attribute->getAttributeId(),
                    'label' => 'test_configurable',
                    'position' => 0,
                    'is_use_default' => true,
                    'values' => [
                        [
                            'value_index' => $attribute->getOptions()[1]->getValue()
                        ]
                    ]
                ]
            ]
        ];
        $response = $this->saveConfigurableProductsBySku($requestData, 'options');
        $this->assertArrayHasKey(self::BULK_UUID_KEY, $response);
        $this->assertNotNull($response[self::BULK_UUID_KEY]);

        $this->assertCount(1, $response['request_items']);
        $this->assertEquals('accepted', $response['request_items'][0]['status']);
        $this->assertFalse($response['errors']);
    }

    /**
     * @param $requestData
     * @param string $urlParam
     * @param string|null $storeCode
     * @return mixed
     */
    private function saveConfigurableProductsBySku($requestData, $urlParam, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_BULK_RESOURCE_PATH.'/bySku/'.$urlParam,
                'httpMethod'   => Request::HTTP_METHOD_POST,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }
}
