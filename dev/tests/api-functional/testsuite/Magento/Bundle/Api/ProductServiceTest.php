<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

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
        $this->markTestSkipped('Processing of custom attributes has been changed in MAGETWO-34448.');
        $bundleProductOptions = [
            [
                "title" => "test option",
                "type" => "checkbox",
                "required" => true,
                "product_links" => [
                    [
                        "sku" => 'simple',
                        "qty" => 1,
                        'is_default' => false,
                        'price' => 1.0,
                        'price_type' => 1
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
            "extension_attributes" => [
                "price_type" => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC,
                "bundle_product_options" => $bundleProductOptions,
                "price_view" => "test"
            ],
        ];

        $response = $this->createProduct($product);

        $this->assertEquals($uniqueId, $response[ProductInterface::SKU]);
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertEquals($bundleProductOptions, $resultBundleProductOptions);
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);

        $response = $this->getProduct($uniqueId);
        $resultBundleProductOptions
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["bundle_product_options"];
        $this->assertEquals('simple', $resultBundleProductOptions[0]["product_links"][0]["sku"]);
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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $productSku]) : $this->_webApiCall($serviceInfo);

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
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
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
