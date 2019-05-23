<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Product repository.
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Custom option factory.
     *
     * @var ProductCustomOptionInterfaceFactory
     */
    private $customOptionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->customOptionFactory = Bootstrap::getObjectManager()->create(ProductCustomOptionInterfaceFactory::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * Tests removing ineligible characters from file_extension.
     *
     * @param string $rawExtensions
     * @param string $expectedExtensions
     * @dataProvider fileExtensionsDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testFileExtensions(string $rawExtensions, string $expectedExtensions)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        /** @var \Magento\Catalog\Model\Product\Option $fileOption */
        $fileOption = $this->createFileOption($rawExtensions);
        $product->addOption($fileOption);
        $product->save();
        $product = $this->productRepository->get('simple');
        $fileOption = $product->getOptions()[0];
        $actualExtensions = $fileOption->getFileExtension();
        $this->assertEquals($expectedExtensions, $actualExtensions);
    }

    /**
     * Data provider for testFileExtensions.
     *
     * @return array
     */
    public function fileExtensionsDataProvider()
    {
        return [
            ['JPG, PNG, GIF', 'jpg, png, gif'],
            ['jpg, jpg, jpg', 'jpg'],
            ['jpg, png, gif', 'jpg, png, gif'],
            ['jpg png gif', 'jpg, png, gif'],
            ['!jpg@png#gif%', 'jpg, png, gif'],
            ['jpg, png, 123', 'jpg, png, 123'],
            ['', ''],
        ];
    }

    /**
     * Create file type option for product.
     *
     * @param string $rawExtensions
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface|void
     */
    private function createFileOption(string $rawExtensions)
    {
        $data = [
            'title' => 'file option',
            'type' => 'file',
            'is_require' => true,
            'sort_order' => 3,
            'price' => 30.0,
            'price_type' => 'percent',
            'sku' => 'sku3',
            'file_extension' => $rawExtensions,
            'image_size_x' => 10,
            'image_size_y' => 20,
        ];

        return $this->customOptionFactory->create(['data' => $data]);
    }

    /**
     * Test to save option price by store
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoConfigFixture default_store catalog/price/scope 1
     * @magentoConfigFixture secondstore_store catalog/price/scope 1
     */
    public function testSaveOptionPriceByStore()
    {
        $secondWebsitePrice = 22.0;
        $defaultStoreId = $this->storeManager->getStore()->getId();
        $secondStoreId = $this->storeManager->getStore('secondstore')->getId();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        $option = $product->getOptions()[0];
        $defaultPrice = $option->getPrice();

        $option->setPrice($secondWebsitePrice);
        $product->setStoreId($secondStoreId);
        // set Current store='secondstore' to correctly save product options for 'secondstore'
        $this->storeManager->setCurrentStore($secondStoreId);
        $this->productRepository->save($product);
        $this->storeManager->setCurrentStore($defaultStoreId);

        $product = $this->productRepository->get('simple', false, Store::DEFAULT_STORE_ID, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($defaultPrice, $option->getPrice(), 'Price value by default store is wrong');

        $product = $this->productRepository->get('simple', false, $secondStoreId, true);
        $option = $product->getOptions()[0];
        $this->assertEquals($secondWebsitePrice, $option->getPrice(), 'Price value by store_id=1 is wrong');
    }
}
