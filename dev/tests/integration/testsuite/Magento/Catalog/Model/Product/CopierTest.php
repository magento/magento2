<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\ResourceModel\UpdateHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests product copier.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CopierTest extends TestCase
{
    /**
     * @var string|null
     */
    private $duplicateSku;

    /**
     * @var string[]
     */
    private $duplicateMediaFiles = [];

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->duplicateSku !== null) {
            try {
                Bootstrap::getObjectManager()->get(ProductRepository::class)->deleteById($this->duplicateSku);
            } catch (NoSuchEntityException $e) {
                // already deleted
            }
        }

        if ($this->duplicateMediaFiles) {
            $objectManager = Bootstrap::getObjectManager();
            $mediaConfig = $objectManager->get(MediaConfig::class);
            $mediaDirectory = $objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
            foreach ($this->duplicateMediaFiles as $file) {
                $mediaDirectory->getDriver()->deleteFile(
                    $mediaDirectory->getAbsolutePath($mediaConfig->getMediaPath($file))
                );
            }
        }

        parent::tearDown();
    }

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
        $thumbnailFile = $product->getData('thumbnail');

        $eavUpdateHandler->execute(
            ProductInterface::class,
            [
                $linkField => $product->getData($linkField),
                'store_id' => $secondStoreId,
                'small_image' => $thumbnailFile,
            ]
        );

        $sourceStoreView = $productRepository->getById($product->getId(), false, $secondStoreId);
        $this->assertEquals($thumbnailFile, $sourceStoreView->getSmallImage());

        $duplicate = $copier->copy($product);

        $duplicateDefault = $productRepository->getById($duplicate->getId(), false, Store::DEFAULT_STORE_ID);
        foreach ($duplicateDefault->getMediaGalleryImages() as $image) {
            $this->duplicateMediaFiles[] = $image->getData('file');
        }

        $duplicateStoreView = $productRepository->getById($duplicate->getId(), false, $secondStoreId);
        $this->duplicateSku = $duplicate->getSku();

        $this->assertNotContains($thumbnailFile, $this->duplicateMediaFiles);
        $this->assertContains($duplicateStoreView->getSmallImage(), $this->duplicateMediaFiles);
    }
}
