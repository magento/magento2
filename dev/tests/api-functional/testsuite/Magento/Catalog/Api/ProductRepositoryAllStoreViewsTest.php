<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests for products creation for all store views.
 *
 * @magentoAppIsolation enabled
 */
class ProductRepositoryAllStoreViewsTest extends WebapiAbstract
{
    const PRODUCT_SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const PRODUCTS_RESOURCE_PATH = '/V1/products';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $productSku = 'simple';

    /**
     * @var Link
     */
    private $productWebsiteLink;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productWebsiteLink = $this->objectManager->get(Link::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->productRepository->delete(
            $this->productRepository->get($this->productSku)
        );
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     */
    public function testCreateProduct(): void
    {
        $productData = $this->getProductData();
        $resultData = $this->saveProduct($productData);
        $this->assertProductWebsites($this->productSku, $this->getAllWebsiteIds());
        $this->assertProductData($productData, $resultData, $this->getAllWebsiteIds());
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     */
    public function testCreateProductOnMultipleWebsites(): void
    {
        $productData = $this->getProductData();
        $resultData = $this->saveProduct($productData);
        $this->assertProductWebsites($this->productSku, $this->getAllWebsiteIds());
        $this->assertProductData($productData, $resultData, $this->getAllWebsiteIds());
    }

    /**
     * Saves Product via API.
     *
     * @param $product
     * @return array
     */
    private function saveProduct($product): array
    {
        $serviceInfo = [
            'rest' => ['resourcePath' =>self::PRODUCTS_RESOURCE_PATH, 'httpMethod' => Request::HTTP_METHOD_POST],
            'soap' => [
                'service' => self::PRODUCT_SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::PRODUCT_SERVICE_NAME . 'Save'
            ]
        ];
        $requestData = ['product' => $product];
        return $this->_webApiCall($serviceInfo, $requestData, null, 'all');
    }

    /**
     * Returns product data.
     *
     * @return array
     */
    private function getProductData(): array
    {
        return [
                'sku' => $this->productSku,
                'name' => 'simple',
                'type_id' => Type::TYPE_SIMPLE,
                'weight' => 1,
                'attribute_set_id' => 4,
                'price' => 10,
                'status' => 1,
                'visibility' => 4,
                'extension_attributes' => [
                    'stock_item' => ['is_in_stock' => true, 'qty' => 1000]
                ],
                'custom_attributes' => [
                    ['attribute_code' => 'url_key', 'value' => 'simple'],
                    ['attribute_code' => 'tax_class_id', 'value' => 2],
                    ['attribute_code' => 'category_ids', 'value' => [333]]
                ]
        ];
    }

    /**
     * Asserts that product is linked to websites in 'catalog_product_website' table.
     *
     * @param string $sku
     * @param array $websiteIds
     * @return void
     */
    private function assertProductWebsites(string $sku, array $websiteIds): void
    {
        $productId = $this->productRepository->get($sku)->getId();
        $this->assertEquals($websiteIds, $this->productWebsiteLink->getWebsiteIdsByProductId($productId));
    }

    /**
     * Asserts result after product creation.
     *
     * @param array $productData
     * @param array $resultData
     * @param array $websiteIds
     * @return void
     */
    private function assertProductData(array $productData, array $resultData, array $websiteIds): void
    {
        foreach ($productData as $key => $value) {
            if ($key == 'extension_attributes' || $key == 'custom_attributes') {
                continue;
            }
            $this->assertEquals($value, $resultData[$key]);
        }
        foreach ($productData['custom_attributes'] as $attribute) {
            $resultAttribute = $this->getCustomAttributeByCode(
                $resultData['custom_attributes'],
                $attribute['attribute_code']
            );
            $this->assertEquals($attribute['value'], $resultAttribute['value']);
        }
        foreach ($productData['extension_attributes']['stock_item'] as $key => $value) {
            $this->assertEquals($value, $resultData['extension_attributes']['stock_item'][$key]);
        }
        $this->assertEquals($websiteIds, $resultData['extension_attributes']['website_ids']);
    }

    /**
     * Get list of all websites IDs.
     *
     * @return array
     */
    private function getAllWebsiteIds(): array
    {
        $websiteIds = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    /**
     * Returns custom attribute data by given code.
     *
     * @param array $attributes
     * @param string $attributeCode
     * @return array
     */
    private function getCustomAttributeByCode(array $attributes, string $attributeCode): array
    {
        $items = array_filter(
            $attributes,
            function ($attribute) use ($attributeCode) {
                return $attribute['attribute_code'] == $attributeCode;
            }
        );

        return reset($items);
    }
}
