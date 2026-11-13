<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Product\MediaGalleryValue;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Media gallery calls on a store view keep the store view values of the images they do not change.
 *
 * @magentoAppArea webapi_rest
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryManagementStoreViewTest extends TestCase
{
    private const STORE_ID = 1;
    private const STORE_LABEL = 'Label A store view';
    private const STORE_POSITION = 5;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    private $galleryManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->galleryManagement = $this->objectManager->get(ProductAttributeMediaGalleryManagementInterface::class);
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [[], []]], as: 'product'),
    ]
    public function testUpdateKeepsStoreViewValuesOfOtherImages(): void
    {
        [$sku, $imageA, $imageB] = $this->prepareProduct();

        $entry = $this->galleryManagement->get($sku, $imageB);
        $entry->setLabel('Label B store view');
        $this->galleryManagement->update($sku, $entry);

        $this->assertStoreViewValuesKept($sku, $imageA);
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [[], []]], as: 'product'),
    ]
    public function testRemoveKeepsStoreViewValuesOfOtherImages(): void
    {
        [$sku, $imageA, $imageB] = $this->prepareProduct();

        $this->galleryManagement->remove($sku, $imageB);

        $this->assertStoreViewValuesKept($sku, $imageA);
    }

    #[
        AppIsolation(true),
        DataFixture(ProductFixture::class, ['media_gallery_entries' => [[], []]], as: 'product'),
    ]
    public function testCreateKeepsStoreViewValuesOfOtherImages(): void
    {
        [$sku, $imageA] = $this->prepareProduct();

        $content = $this->objectManager->get(ImageContentInterfaceFactory::class)->create();
        $content->setName('new_image.jpg')
            ->setType('image/jpeg')
            ->setBase64EncodedData(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                base64_encode(file_get_contents(__DIR__ . '/../../../_files/magento_image.jpg'))
            );
        /** @var ProductAttributeMediaGalleryEntryInterface $entry */
        $entry = $this->objectManager->get(ProductAttributeMediaGalleryEntryInterfaceFactory::class)->create();
        $entry->setMediaType('image')
            ->setLabel('New image')
            ->setPosition(3)
            ->setDisabled(false)
            ->setTypes([])
            ->setContent($content);
        $this->galleryManagement->create($sku, $entry);

        $this->assertStoreViewValuesKept($sku, $imageA);
    }

    /**
     * Give the first image store view values on the default store view and switch to that store view
     *
     * @return array SKU, id of the first image, id of the second image
     */
    private function prepareProduct(): array
    {
        $sku = DataFixtureStorageManager::getStorage()->get('product')->getSku();
        $product = $this->objectManager->get(ProductRepositoryInterface::class)->get($sku, false, 0, true);
        $imageIds = array_map(
            static fn (ProductAttributeMediaGalleryEntryInterface $entry): int => (int)$entry->getId(),
            $product->getMediaGalleryEntries()
        );
        sort($imageIds);
        [$imageA, $imageB] = $imageIds;

        $this->objectManager->get(Gallery::class)->insertGalleryValueInStore(
            [
                'value_id' => $imageA,
                'store_id' => self::STORE_ID,
                $this->getLinkField() => $product->getData($this->getLinkField()),
                'label' => self::STORE_LABEL,
                'position' => self::STORE_POSITION,
                'disabled' => 1,
            ]
        );
        $this->objectManager->get(StoreManagerInterface::class)->setCurrentStore(self::STORE_ID);

        return [$sku, $imageA, $imageB];
    }

    /**
     * Assert the store view values of an image are still the ones set in prepareProduct()
     *
     * @param string $sku
     * @param int $imageId
     * @return void
     */
    private function assertStoreViewValuesKept(string $sku, int $imageId): void
    {
        $product = $this->objectManager->get(ProductRepositoryInterface::class)->get($sku, false, 0, true);
        $storeValues = $this->objectManager->get(MediaGalleryValue::class)->getAllByEntityIdAndStoreId(
            (int)$product->getData($this->getLinkField()),
            self::STORE_ID
        );
        $storeValue = current(array_filter($storeValues, static fn (array $row) => (int)$row['value_id'] === $imageId));

        $this->assertNotFalse($storeValue, 'The store view row of the image is missing.');
        $this->assertEquals(self::STORE_LABEL, $storeValue['label']);
        $this->assertEquals(self::STORE_POSITION, $storeValue['position']);
        $this->assertEquals(1, $storeValue['disabled']);
    }

    /**
     * Get the product link field, entity_id or row_id
     *
     * @return string
     */
    private function getLinkField(): string
    {
        return $this->objectManager->get(MetadataPool::class)->getMetadata(ProductInterface::class)->getLinkField();
    }
}
