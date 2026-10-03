<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\ResourceModel\UpdateHandler;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests product copier.
 */
class CopierTest extends TestCase
{
    /**
     * Tests copying of product.
     *
     * Case when url_key is set for store view and has equal value to default store.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore_with_url_key.php
     * @magentoAppArea adminhtml
     */
    public function testProductCopyWithExistingUrlKey()
    {
        $productSKU = 'simple_100';
        /** @var ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        $copier = Bootstrap::getObjectManager()->get(Copier::class);

        $product = $productRepository->get($productSKU);
        $duplicate = $copier->copy($product);

        $duplicateStoreView = $productRepository->getById($duplicate->getId(), false, Store::DISTRO_STORE_ID);
        $productStoreView = $productRepository->get($productSKU, false, Store::DISTRO_STORE_ID);

        $this->assertNotEquals(
            $duplicateStoreView->getUrlKey(),
            $productStoreView->getUrlKey(),
            'url_key of product duplicate should be different then url_key of the product for the same store view'
        );
    }

    /**
     * Tests that a store view scoped image role is remapped to the duplicate's own gallery file.
     */
    #[
        AppArea('adminhtml'),
        DataFixture('Magento/Catalog/_files/product_with_multiple_images.php'),
        DataFixture('Magento/Store/_files/second_store.php'),
    ]
    public function testProductCopyWithStoreViewImageRole()
    {
        $objectManager = Bootstrap::getObjectManager();
        $productRepository = $objectManager->get(ProductRepository::class);
        $copier = $objectManager->get(Copier::class);
        $storeRepository = $objectManager->get(StoreRepositoryInterface::class);
        $eavUpdateHandler = $objectManager->get(UpdateHandler::class);

        $secondStoreId = (int) $storeRepository->get('fixture_second_store')->getId();
        $product = $productRepository->get('simple');
        $linkField = $product->getResource()->getLinkField();

        $eavUpdateHandler->execute(
            ProductInterface::class,
            [
                $linkField => $product->getData($linkField),
                'store_id' => $secondStoreId,
                'small_image' => '/m/a/magento_thumbnail.jpg',
            ]
        );

        $sourceStoreView = $productRepository->getById($product->getId(), false, $secondStoreId);
        $this->assertEquals('/m/a/magento_thumbnail.jpg', $sourceStoreView->getSmallImage());

        $duplicate = $copier->copy($product);

        $duplicateDefault = $productRepository->getById($duplicate->getId(), false, Store::DEFAULT_STORE_ID);
        $duplicateFiles = [];
        foreach ($duplicateDefault->getMediaGalleryImages() as $image) {
            $duplicateFiles[] = $image->getData('file');
        }

        $duplicateStoreView = $productRepository->getById($duplicate->getId(), false, $secondStoreId);

        $this->assertNotContains('/m/a/magento_thumbnail.jpg', $duplicateFiles);
        $this->assertContains($duplicateStoreView->getSmallImage(), $duplicateFiles);
    }
}
