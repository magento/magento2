<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test product data mapper
 */
class ProductDataMapperTest extends TestCase
{
    /**
     * @var ProductDataMapper
     */
    private $model;
    /**
     * @var Config
     */
    private $eavConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $additionalFieldsProvider = $this->createMock(AdditionalFieldsProviderInterface::class);
        $additionalFieldsProvider->method('getFields')->willReturn([]);
        $this->model = $this->objectManager->create(
            ProductDataMapper::class,
            [
                'additionalFieldsProvider' => $additionalFieldsProvider,
            ]
        );
        $this->eavConfig = $this->objectManager->get(Config::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test mapping select attribute with different store labels
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoDataFixture Magento/Elasticsearch/_files/select_attribute_store_labels.php
     * @magentoConfigFixture default/catalog/search/engine elasticsearch
     */
    public function testMapSelectAttributeWithDifferentStoreLabels(): void
    {
        $product = $this->productRepository->get('simple');
        $productId = $product->getId();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'select_attribute');
        $defaultStore = $this->storeManager->getStore('default');
        $secondStore = $this->storeManager->getStore('fixture_second_store');
        $attributeId = $attribute->getId();
        $attributeValue = $this->getAttributeOptionValue($attribute, 'Table');
        $defaultStoreMap = [
            $productId => [
                'store_id' => $defaultStore->getId(),
                'select_attribute' => (int)$attributeValue,
                'select_attribute_value' => 'Table_default',
            ],
        ];
        $secondStoreMap = [
            $productId => [
                'store_id' => $secondStore->getId(),
                'select_attribute' => (int)$attributeValue,
                'select_attribute_value' => 'Table_fixture_second_store',
            ],
        ];
        $data = [
            $productId => [
                $attributeId => $attributeValue,
            ],
        ];
        $this->assertSame($defaultStoreMap, $this->model->map($data, $defaultStore->getId(), []));
        $this->assertSame($secondStoreMap, $this->model->map($data, $secondStore->getId(), []));
    }

    /**
     * Get attribute option value
     *
     * @param AbstractAttribute $attribute
     * @param string $text
     * @return string|null
     */
    private function getAttributeOptionValue(
        AbstractAttribute $attribute,
        string $text
    ): ?string {
        $value = null;
        $attribute->setStoreId(0);
        foreach ($attribute->getOptions() as $option) {
            if ($option->getLabel() === $text) {
                $value = $option->getValue();
                break;
            }
        }
        return $value;
    }
}
