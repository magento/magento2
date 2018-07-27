<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Option;

use Magento\Catalog\Model\Product\Option;

/**
 * Tests Magento\Catalog\Model\ResourceModel\Product\Option\Value.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_dropdown_option.php
 * @magentoDataFixture Magento/Store/_files/second_store.php
 */
class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $this->storeManager = $this->objectManager->create(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->store = $this->objectManager->create(\Magento\Store\Model\Store::class)
            ->load('fixture_second_store', 'code');

        $this->objectManager->create(\Magento\CatalogSearch\Model\Indexer\Fulltext\Processor::class)
            ->reindexAll();
    }

    /**
     * @return void
     */
    public function testSaveValueTitleCustomStore()
    {
        $this->saveOptionValue($this->store, ['title' => 'option 1 Custom Store']);

        self::assertEquals('drop_down option 1', $this->getValueTitle());
        self::assertEquals('option 1 Custom Store', $this->getValueTitle($this->store->getStoreId()));
    }

    /**
     * @return void
     */
    public function testRemoveValueTitleCustomStore()
    {
        $this->saveOptionValue($this->store, ['title' => 'option 1 Custom Store']);
        $this->saveOptionValue($this->store, ['title' => 'option 1 Custom Store', 'is_delete_store_title' => 1]);

        self::assertEquals('drop_down option 1', $this->getValueTitle());
        self::assertEquals('drop_down option 1', $this->getValueTitle($this->store->getStoreId()));
    }

    /**
     * Returns option value title.
     *
     * @param int|null $storeId
     * @return string|null
     */
    private function getValueTitle($storeId = null)
    {
        $product = $this->productRepository->get('simple_dropdown_option', false, $storeId, true);
        /** @var \Magento\Catalog\Model\Product\Option $option */
        $option = $product->getOptions()[0];
        $values = $option->getValues();
        /** @var \Magento\Catalog\Model\Product\Option\Value $value */
        $value = reset($values);

        return $value->getTitle();
    }

    /**
     * Save option value.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param array $valueData
     * @return void
     */
    private function saveOptionValue(\Magento\Store\Api\Data\StoreInterface $store, $valueData = [])
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple_dropdown_option');
        /** @var \Magento\Catalog\Model\Product\Option $option */
        $option = $product->getOptions()[0];
        $values = $option->getValues();
        /** @var \Magento\Catalog\Model\Product\Option\Value $value */
        $value = reset($values);
        $value->addData($valueData);

        // Update option title in custom store scope
        $this->storeManager->setCurrentStore($store);
        $this->productRepository->save($product);
        $this->storeManager->setCurrentStore($defaultStore);
    }
}
