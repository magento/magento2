<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Option;

/**
 * Tests Magento\Catalog\Model\ResourceModel\Product\Option
 *
 * @magentoDataFixture Magento/Catalog/_files/product_with_dropdown_option.php
 * @magentoDataFixture Magento/Store/_files/second_store.php
 */
class OptionTest extends \PHPUnit_Framework_TestCase
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
    public function testSaveOptionTitleCustomStore()
    {
        $this->saveOption($this->store, ['title' => 'drop_down option Custom Store']);

        self::assertEquals('drop_down option', $this->getOptionTitle());
        self::assertEquals('drop_down option Custom Store', $this->getOptionTitle($this->store->getStoreId()));
    }

    /**
     * @return void
     */
    public function testRemoveOptionTitleCustomStore()
    {
        $this->saveOption($this->store, ['title' => 'drop_down option Custom Store']);
        $this->saveOption($this->store, ['title' => 'drop_down option Custom Store', 'is_delete_store_title' => 1]);

        self::assertEquals('drop_down option', $this->getOptionTitle());
        self::assertEquals('drop_down option', $this->getOptionTitle($this->store->getStoreId()));
    }

    /**
     * Returns option title.
     *
     * @param int|null $storeId
     * @return string|null
     */
    private function getOptionTitle($storeId = null)
    {
        $product = $this->productRepository->get('simple_dropdown_option', false, $storeId, true);

        return $product->getOptions()[0]->getTitle();
    }

    /**
     * Save option.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param array $optionData
     * @return void
     */
    private function saveOption(\Magento\Store\Api\Data\StoreInterface $store, $optionData = [])
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple_dropdown_option');
        $product->getOptions()[0]->addData($optionData);

        // Update option title in custom store scope
        $this->storeManager->setCurrentStore($store);
        $this->productRepository->save($product);
        $this->storeManager->setCurrentStore($defaultStore);
    }
}
