<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Bundle\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

/**
 * Class ProductServiceTest for testing Bundle Product API
 */
class ProductServiceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $productCollection;

    /**
     * Execute per test initialization
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productCollection = $objectManager->get('Magento\Catalog\Model\Resource\Product\Collection');
    }

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $this->productCollection->addFieldToFilter(
            'sku',
            ['in' => ['sku-test-product-bundle']]
        )->delete();
        unset($this->productCollection);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_new.php
     */
    public function testCreateBundle()
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestIncomplete('MAGETWO-31016: incompatible with ZF 1.12.9');
        }
        $bundleProductOptions = [
            "attribute_code" => "bundle_product_options",
            "value" => [
                [
                    "title" => "test option",
                    "type" => "checkbox",
                    "required" => 1,
                    "product_links" => [
                        [
                            "sku" => 'simple',
                            "qty" => 1,
                        ],
                    ],
                ],
            ],
        ];

        $uniqueId = 'sku-test-product-bundle';
        $product = [
            "sku" => $uniqueId,
            "name" => $uniqueId,
            "type_id" => "bundle",
            "price" => 50,
            'attribute_set_id' => 4,
            "custom_attributes" => [
                "price_type" => [
                    'attribute_code' => 'price_type',
                    'value' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
                ],
                "bundle_product_options" => $bundleProductOptions,
                "price_view" => [
                    "attribute_code" => "price_view",
                    "value" => "test",
                ],
            ],
        ];

        $response = $this->createProduct($product);

        $this->assertEquals($uniqueId, $response[ProductInterface::SKU]);
        $this->assertEquals(
            $bundleProductOptions,
            $response[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]["bundle_product_options"]
        );

        $response = $this->getProduct($uniqueId);
        $foundBundleProductOptions = false;
        foreach ($response[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY] as $customAttribute) {
            if ($customAttribute["attribute_code"] === 'bundle_product_options') {
                $this->assertEquals('simple', $customAttribute["value"][0]["product_links"][0]["sku"]);
                $foundBundleProductOptions = true;
            }
        }
        $this->assertTrue($foundBundleProductOptions);
    }

    /**
     * Get product
     *
     * @param string $productSku
     * @return array the product data
     */
    protected function getProduct($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['productSku' => $productSku]) : $this->_webApiCall($serviceInfo);

        return $response;
    }

    /**
     * Create product
     *
     * @param array $product
     * @return array the created product data
     */
    protected function createProduct($product)
    {
        $serviceInfo = [
            'rest' => ['resourcePath' => self::RESOURCE_PATH, 'httpMethod' => RestConfig::HTTP_METHOD_POST],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        $product[ProductInterface::SKU] = $response[ProductInterface::SKU];
        return $product;
    }
}
