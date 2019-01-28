<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\TestFramework\Indexer\TestCase as IndexerTestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Catalog\Model\ResourceModel\Product\Flat as FlatResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Custom Flat Attribute Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomFlatAttributeTest extends IndexerTestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var FlatResource
     */
    private $flatResource;

    /**
     * @var FlatState
     */
    private $flatState;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $savedCurrentStore;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->processor = $this->objectManager->get(Processor::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->flatResource = $this->objectManager->get(FlatResource::class);
        $this->flatState = $this->objectManager->create(FlatState::class, [
            'isAvailable' => true
        ]);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->savedCurrentStore = $this->storeManager->getStore();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->storeManager->setCurrentStore($this->savedCurrentStore);
    }

    /**
     * Tests that custom product attribute will appear in flat table and can be updated in it.
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testProductUpdateCustomAttribute()
    {
        $product = $this->productRepository->get('simple_with_custom_flat_attribute');
        $product->setCustomAttribute('flat_attribute', 'changed flat attribute');
        $this->productRepository->save($product);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        /** @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria */
        $searchCriteria = $searchCriteriaBuilder->addFilter('sku', 'simple_with_custom_flat_attribute')
            ->create();

        $items = $this->productRepository->getList($searchCriteria)
            ->getItems();
        $product = reset($items);
        $resourceModel = $product->getResourceCollection()
            ->getEntity();

        self::assertInstanceOf(
            FlatResource::class,
            $resourceModel,
            'Product should be received from flat resource'
        );

        self::assertEquals(
            'changed flat attribute',
            $product->getFlatAttribute(),
            'Product flat attribute should be able to change.'
        );
    }

    /**
     * Tests flat dropdown attribute.
     * Tests that flat dropdown attribute will be changed for different flat tables (it means for different stores)
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default_store catalog/frontend/flat_catalog_product 1
     * @magentoConfigFixture default_store catalog/frontend/flat_catalog_category 1
     * @magentoConfigFixture fixturestore_store catalog/frontend/flat_catalog_product 1
     * @magentoConfigFixture fixturestore_store catalog/frontend/flat_catalog_category 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore.php
     * @magentoDataFixture Magento/Catalog/_files/flat_dropdown_attribute.php
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testFlatDropDownAttribute()
    {
        $attribute = $this->attributeRepository->get('flat_attribute');
        $attributeOptions = $attribute->getOptions();

        $firstStoreAttributeOption = $attributeOptions[1];
        $productStore1 = $this->productRepository->get('simple', false, 1);
        $productStore1->setFlatAttribute($firstStoreAttributeOption->getValue());
        $this->productRepository->save($productStore1);

        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $store = $storeRepository->get('fixturestore');

        $secondStoreAttributeOption = $attributeOptions[2];
        $productStore2 = $this->productRepository->get('simple', false, $store->getId());
        $productStore2->setFlatAttribute($secondStoreAttributeOption->getValue());
        $this->productRepository->save($productStore2);

        $this->processor = $this->objectManager->create(Processor::class);
        $this->processor->reindexAll();

        $productStore1 = $this->getFlatProductFromStore('simple');
        self::assertEquals($firstStoreAttributeOption->getLabel(), $productStore1->getFlatAttributeValue());

        $productStore2 = $this->getFlatProductFromStore('simple', $store);
        self::assertEquals($secondStoreAttributeOption->getLabel(), $productStore2->getFlatAttributeValue());
    }

    /**
     * Get product from store with flat data
     *
     * @param string $sku
     * @param Store|null $store
     * @return ProductInterface
     */
    private function getFlatProductFromStore(string $sku, $store = null): ProductInterface
    {
        if ($store) {
            $this->storeManager->setCurrentStore($store);
        }

        /** @var Collection $productCollection */
        $productCollection = $this->objectManager->create(
            Collection::class,
            [
                'catalogProductFlatState' => $this->flatState
            ]
        );

        if ($store) {
            $productCollection->setStoreId($store->getId());
        }

        $productCollection->setEntity($this->flatResource);
        $productCollection->addAttributeToFilter('sku', $sku);
        $productCollection->addAttributeToSelect('flat_attribute');

        return $productCollection->load()
            ->getFirstItem();
    }
}
