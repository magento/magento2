<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Link
     */
    private $productWebsiteLink;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var string
     */
    private $productSku = 'simple';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->eavConfig = $this->objectManager->get(Config::class);
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
        try {
            $this->productRepository->deleteById($this->productSku);
        } catch (NoSuchEntityException $e) {
            //already deleted
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/category.php
     * @return void
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
     * @return void
     */
    public function testCreateProductOnMultipleWebsites(): void
    {
        $productData = $this->getProductData();
        $resultData = $this->saveProduct($productData);
        $this->assertProductWebsites($this->productSku, $this->getAllWebsiteIds());
        $this->assertProductData($productData, $resultData, $this->getAllWebsiteIds());
    }

    /**
     * Saves product via API.
     *
     * @param array $product
     * @return array
     */
    private function saveProduct(array $product): array
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
        $setId = (int)$this->eavConfig->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();

        return [
                ProductInterface::SKU => $this->productSku,
                ProductInterface::NAME => 'simple',
                ProductInterface::TYPE_ID => Type::TYPE_SIMPLE,
                ProductInterface::WEIGHT => 1,
                ProductInterface::ATTRIBUTE_SET_ID => $setId,
                ProductInterface::PRICE => 10,
                ProductInterface::STATUS => Status::STATUS_ENABLED,
                ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH,
                ProductInterface::EXTENSION_ATTRIBUTES_KEY => [
                    'stock_item' => [
                        StockItemInterface::IS_IN_STOCK => 1,
                        StockItemInterface::QTY => 1000,
                        StockItemInterface::IS_QTY_DECIMAL => 0,
                        StockItemInterface::SHOW_DEFAULT_NOTIFICATION_MESSAGE => 0,
                        StockItemInterface::USE_CONFIG_MIN_QTY => 0,
                        StockItemInterface::USE_CONFIG_MIN_SALE_QTY => 0,
                        StockItemInterface::MIN_QTY => 1,
                        StockItemInterface::MIN_SALE_QTY => 1,
                        StockItemInterface::MAX_SALE_QTY => 100,
                        StockItemInterface::USE_CONFIG_MAX_SALE_QTY => 0,
                        StockItemInterface::USE_CONFIG_BACKORDERS => 0,
                        StockItemInterface::BACKORDERS => 0,
                        StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY => 0,
                        StockItemInterface::NOTIFY_STOCK_QTY => 0,
                        StockItemInterface::USE_CONFIG_QTY_INCREMENTS => 0,
                        StockItemInterface::QTY_INCREMENTS => 0,
                        StockItemInterface::USE_CONFIG_ENABLE_QTY_INC => 0,
                        StockItemInterface::ENABLE_QTY_INCREMENTS => 0,
                        StockItemInterface::USE_CONFIG_MANAGE_STOCK => 1,
                        StockItemInterface::MANAGE_STOCK => 1,
                        StockItemInterface::LOW_STOCK_DATE => null,
                        StockItemInterface::IS_DECIMAL_DIVIDED => 0,
                        StockItemInterface::STOCK_STATUS_CHANGED_AUTO => 0,
                    ],
                ],
                ProductInterface::CUSTOM_ATTRIBUTES => [
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
            if ($attribute['attribute_code'] == 'category_ids') {
                $this->assertEquals(array_values($attribute['value']), array_values($resultAttribute['value']));
                continue;
            }
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
        foreach ($this->storeManager->getWebsites() as $website) {
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
