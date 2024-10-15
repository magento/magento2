<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\DeleteValidator;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;

/**
 * Tests for \Magento\Catalog\Model\Product\Gallery\GalleryManagement.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryManagementTest extends TestCase
{
    /**
     * @var GalleryManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $mediaGalleryEntryMock;

    /**
     * @var MockObject|AttributeValue
     */
    protected $attributeValueMock;

    /**
     * @var ProductInterfaceFactory|MockObject
     */
    private $productInterfaceFactory;

    /**
     * @var DeleteValidator|MockObject
     */
    private $deleteValidator;

    /**
     * @var ProductInterface|MockObject
     */
    private $newProductMock;

    /**
     * @var ImageContentInterface|MockObject
     */
    private $imageContentInterface;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var Mime|MockObject
     */
    private $mime;

    /**
     * @var File|MockObject
     */
    private $file;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->contentValidatorMock = $this->getMockForAbstractClass(ImageContentValidatorInterface::class);
        $this->productInterfaceFactory = $this->createMock(ProductInterfaceFactory::class);
        $this->deleteValidator = $this->createMock(DeleteValidator::class);
        $this->imageContentInterface = $this->getMockBuilder(ImageContentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->mime = $this->createMock(Mime::class);
        $this->file = $this->createMock(File::class);
        $this->productMock = $this->createPartialMock(
            Product::class,
            [
                'setStoreId',
                'getData',
                'getStoreId',
                'getSku',
                'getCustomAttribute',
                'getMediaGalleryEntries',
                'setMediaGalleryEntries',
                'getMediaAttributes',
                'getMediaConfig'
            ]
        );
        $this->mediaGalleryEntryMock =
            $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $this->model = new GalleryManagement(
            $this->productRepositoryMock,
            $this->contentValidatorMock,
            $this->productInterfaceFactory,
            $this->deleteValidator,
            $this->imageContentInterface,
            $this->filesystem,
            $this->mime,
            $this->file,
        );
        $this->attributeValueMock = $this->getMockBuilder(AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->newProductMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->productInterfaceFactory->method('create')
            ->willReturn($this->newProductMock);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidImageException(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('The image content is invalid. Verify the content and try again.');
        $entryContentMock = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(false);

        $this->model->create("sku", $this->mediaGalleryEntryMock);
    }

    /**
     * @return void
     */
    public function testCreateWithCannotSaveException(): void
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The product can\'t be saved.');
        $productSku = 'mediaProduct';
        $entryContentMock = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $this->productMock->expects($this->any())
            ->method('getMediaAttributes')
            ->willReturn(['small_image' => $attributeMock]);

        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->newProductMock)
            ->willThrowException(new \Exception());
        $this->model->create($productSku, $this->mediaGalleryEntryMock);
    }

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $productSku = 'mediaProduct';
        $entryContentMock = $this->createMock(
            ImageContentInterface::class
        );
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->newProductMock)
            ->willReturn($this->productMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $this->mediaGalleryEntryMock->expects($this->any())->method('getTypes')->willReturn(['small_image']);

        $newEntryMock = $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $newEntryMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->productMock
            ->method('getMediaGalleryEntries')
            ->willReturnOnConsecutiveCalls([], [$newEntryMock]);
        $this->newProductMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$this->mediaGalleryEntryMock]);

        $this->assertEquals(42, $this->model->create($productSku, $this->mediaGalleryEntryMock));
    }

    /**
     * @return void
     */
    public function testUpdateWithNonExistingImage(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');
        $productSku = 'testProduct';
        $entryMock = $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $existingEntryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->model->update($productSku, $entryMock);
    }

    /**
     * @return void
     */
    public function testUpdateWithCannotSaveException(): void
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The product can\'t be saved.');
        $productSku = 'testProduct';
        $entryMock = $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $existingEntryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->newProductMock)
            ->willThrowException(new \Exception());
        $this->model->update($productSku, $entryMock);
    }

    /**
     * Check update gallery entry behavior.
     *
     * @return void
     */
    public function testUpdate(): void
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $entrySecondId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingSecondEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );

        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $existingEntryMock->expects($this->once())->method('getTypes')->willReturn(['small_image']);
        $existingEntryMock->expects($this->once())->method('setTypes')->with(['small_image']);
        $existingSecondEntryMock->expects($this->once())->method('getId')->willReturn($entrySecondId);
        $existingSecondEntryMock->expects($this->once())->method('getTypes')->willReturn(['image']);
        $existingSecondEntryMock->expects($this->once())->method('setTypes')->with([]);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock, $existingSecondEntryMock]);

        $entryMock->expects($this->exactly(2))->method('getId')->willReturn($entryId);
        $entryMock->expects($this->once())->method('getTypes')->willReturn(['image']);

        $this->newProductMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$entryMock, $existingSecondEntryMock])
            ->willReturnSelf();
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->newProductMock);

        $this->assertTrue($this->model->update($productSku, $entryMock));
    }

    /**
     * @return void
     */
    public function testRemoveWithNonExistingImage(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->remove($productSku, $entryId);
    }

    /**
     * @return void
     */
    public function testRemove(): void
    {
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $existingEntryMock->expects($this->once())->method('getFile')->willReturn('path/to/file');
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->newProductMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([]);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->newProductMock);
        $this->assertTrue($this->model->remove($productSku, $entryId));
    }

    /**
     * @return void
     */
    public function testGetWithNonExistingProduct(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The product doesn\'t exist. Verify and try again.');
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willThrowException(new \Exception());
        $this->model->get($productSku, $imageId);
    }

    /**
     * @return void
     */
    public function testGetWithNonExistingImage(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The image doesn\'t exist. Verify and try again.');
        $productSku = 'testProduct';
        $imageId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(44);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->get($productSku, $imageId);
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $mediaConfigMock = $this->getMockBuilder(MediaConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaConfigMock->expects($this->once())
            ->method('getMediaPath')
            ->willReturn("base/path/test123.jpg");
        $this->productMock->expects($this->once())
            ->method('getMediaConfig')
            ->willReturn($mediaConfigMock);
        $mediaDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);
        $mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('base/path/test123.jpg')
            ->willReturn('absolute/path/base/path/test123.jpg');
        $this->file->expects($this->any())
            ->method('getPathInfo')
            ->willReturnCallback(
                function ($path) {
                    return pathinfo($path);
                }
            );
        $driverMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaDirectoryMock->expects($this->any())->method('getDriver')->willReturn($driverMock);
        $driverMock->expects($this->once())
            ->method('fileGetContents')
            ->willReturn('0123456789abcdefghijklmnopqrstuvwxyz');
        $ImageContentInterface = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ImageContentInterface->expects($this->once())
            ->method('setName')
            ->willReturnSelf();
        $ImageContentInterface->expects($this->once())
            ->method('setBase64EncodedData')
            ->willReturnSelf();
        $ImageContentInterface->expects($this->once())
            ->method('setType')
            ->willReturnSelf();
        $this->imageContentInterface->expects($this->once())
            ->method('create')
            ->willReturn($ImageContentInterface);
        $this->assertEquals($existingEntryMock, $this->model->get($productSku, $imageId));
    }

    /**
     * @return void
     */
    public function testGetList(): void
    {
        $productSku = 'testProductSku';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $entryMock = $this->getMockForAbstractClass(ProductAttributeMediaGalleryEntryInterface::class);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $mediaConfigMock = $this->getMockBuilder(MediaConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaConfigMock->expects($this->once())
            ->method('getMediaPath')
            ->willReturn("base/path/test123.jpg");
        $this->productMock->expects($this->once())
            ->method('getMediaConfig')
            ->willReturn($mediaConfigMock);
        $mediaDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($mediaDirectoryMock);
        $mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with('base/path/test123.jpg')
            ->willReturn('absolute/path/base/path/test123.jpg');
        $this->file->expects($this->any())
            ->method('getPathInfo')
            ->willReturnCallback(
                function ($path) {
                    return pathinfo($path);
                }
            );
        $driverMock = $this->getMockBuilder(DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mediaDirectoryMock->expects($this->any())->method('getDriver')->willReturn($driverMock);
        $driverMock->expects($this->once())
            ->method('fileGetContents')
            ->willReturn('0123456789abcdefghijklmnopqrstuvwxyz');
        $ImageContentInterface = $this->getMockBuilder(ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ImageContentInterface->expects($this->once())
            ->method('setName')
            ->willReturnSelf();
        $ImageContentInterface->expects($this->once())
            ->method('setBase64EncodedData')
            ->willReturnSelf();
        $ImageContentInterface->expects($this->once())
            ->method('setType')
            ->willReturnSelf();
        $this->imageContentInterface->expects($this->once())
            ->method('create')
            ->willReturn($ImageContentInterface);
        $this->assertEquals([$entryMock], $this->model->getList($productSku));
    }
}
