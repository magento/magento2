<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Catalog\Model\Product\Gallery\CreateHandler.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 * @magentoDataFixture Magento/Catalog/_files/product_image.php
 */
class CreateHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $fileName = '/m/a/magento_image.jpg';

    private $fileLabel = 'Magento image';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CreateHandler
     */
    private $createHandler;

    /**
     * @var Gallery
     */
    private $galleryResource;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->createHandler = $this->objectManager->create(CreateHandler::class);
        $this->galleryResource = $this->objectManager->create(Gallery::class);
    }

    /**
     * @covers CreateHandler::execute
     *
     * @return void
     */
    public function testExecuteWithImageDuplicate(): void
    {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, 'label' => $this->fileLabel]]]
        );
        $product->setData('image', $this->fileName);
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals($this->fileLabel, $product->getData('image_label'));

        $product->setIsDuplicate(true);
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['value_id' => '100', 'file' => $this->fileName, 'label' => $this->fileLabel]]]
        );
        $this->createHandler->execute($product);
        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/duplicate/100'));
        $this->assertEquals($this->fileLabel, $product->getData('image_label'));
    }

    /**
     * Check sanity of posted image file name
     *
     * @param string $imageFileName
     * @expectedException FileSystemException
     * @dataProvider illegalFilenameDataProvider
     */
    public function testExecuteWithIllegalFilename(string $imageFileName): array
    {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $imageFileName, 'label' => 'New image']]]
        );
        $product->setData('image', $imageFileName);

        try {
            $this->createHandler->execute($product);
        } catch (FileSystemException $exception) {
            $this->assertContains(" file doesn't exist.", $exception->getLogMessage());
            $this->assertNotContains('../', $exception->getLogMessage());
            throw $exception;
        }
    }

    /**
     * @return array
     */
    public function illegalFilenameDataProvider(): array
    {
        return [
            ['../../../../../.htaccess'],
            ['/../../../.././.htaccess.tmp'],
        ];
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $image
     * @param string $smallImage
     * @param string $swatchImage
     * @param string $thumbnail
     * @return void
     */
    public function testExecuteWithImageRoles(
        string $image,
        string $smallImage,
        string $swatchImage,
        string $thumbnail
    ): void {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, 'label' => '']]]
        );
        $product->setData('image', $image);
        $product->setData('small_image', $smallImage);
        $product->setData('swatch_image', $swatchImage);
        $product->setData('thumbnail', $thumbnail);
        $this->createHandler->execute($product);

        $this->assertMediaImageRoleAttributes($product, $image, $smallImage, $swatchImage, $thumbnail);
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $image
     * @param string $smallImage
     * @param string $swatchImage
     * @param string $thumbnail
     * @return void
     */
    public function testExecuteWithoutImages(
        string $image,
        string $smallImage,
        string $swatchImage,
        string $thumbnail
    ): void {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, 'label' => '']]]
        );
        $product->setData('image', $image);
        $product->setData('small_image', $smallImage);
        $product->setData('swatch_image', $swatchImage);
        $product->setData('thumbnail', $thumbnail);
        $this->createHandler->execute($product);

        $product->unsetData('image');
        $product->unsetData('small_image');
        $product->unsetData('swatch_image');
        $product->unsetData('thumbnail');
        $this->createHandler->execute($product);

        $this->assertMediaImageRoleAttributes($product, $image, $smallImage, $swatchImage, $thumbnail);
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                'image' => $this->fileName,
                'small_image' => $this->fileName,
                'swatch_image' => $this->fileName,
                'thumbnail' => $this->fileName
            ],
            [
                'image' => 'no_selection',
                'small_image' => 'no_selection',
                'swatch_image' => 'no_selection',
                'thumbnail' => 'no_selection'
            ]
        ];
    }

    /**
     * @dataProvider additionalGalleryFieldsProvider
     * @param string $mediaField
     * @param string $value
     * @param string|null $expectedValue
     * @return void
     */
    public function testExecuteWithAdditionalGalleryFields(
        string $mediaField,
        string $value,
        ?string $expectedValue
    ): void {
        $product = $this->getProduct();
        $product->setData(
            'media_gallery',
            ['images' => ['image' => ['file' => $this->fileName, $mediaField => $value]]]
        );
        $this->createHandler->execute($product);
        $galleryAttributeId = $product->getResource()->getAttribute('media_gallery')->getAttributeId();
        $productImages = $this->galleryResource->loadProductGalleryByAttributeId($product, $galleryAttributeId);
        $image = reset($productImages);
        $this->assertEquals($image[$mediaField], $expectedValue);
    }

    /**
     * @return array
     */
    public function additionalGalleryFieldsProvider(): array
    {
        return [
            ['label', '', null],
            ['label', 'Some label', 'Some label'],
            ['disabled', '0', '0'],
            ['disabled', '1', '1'],
            ['position', '1', '1'],
            ['position', '2', '2'],
        ];
    }

    /**
     * @return Product
     */
    private function getProduct(): Product
    {
        /** @var $product Product */
        $product = $this->objectManager->create(Product::class);
        $product->load(1);
        return $product;
    }

    /**
     * @param string $image
     * @param string $smallImage
     * @param string $swatchImage
     * @param string $thumbnail
     * @param Product $product
     * @return void
     */
    private function assertMediaImageRoleAttributes(
        Product $product,
        string $image,
        string $smallImage,
        string $swatchImage,
        string $thumbnail
    ): void {
        $resource = $product->getResource();
        $id = $product->getId();
        $storeId = $product->getStoreId();

        $this->assertStringStartsWith('/m/a/magento_image', $product->getData('media_gallery/images/image/new_file'));
        $this->assertEquals(
            $image,
            $resource->getAttributeRawValue($id, $resource->getAttribute('image'), $storeId)
        );
        $this->assertEquals(
            $smallImage,
            $resource->getAttributeRawValue($id, $resource->getAttribute('small_image'), $storeId)
        );
        $this->assertEquals(
            $swatchImage,
            $resource->getAttributeRawValue($id, $resource->getAttribute('swatch_image'), $storeId)
        );
        $this->assertEquals(
            $thumbnail,
            $resource->getAttributeRawValue($id, $resource->getAttribute('thumbnail'), $storeId)
        );
    }
}
