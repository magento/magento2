<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

/**
 * Tests for \Magento\Catalog\Model\Product\Gallery\GalleryManagement.
 */
class GalleryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\GalleryManagement
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $mediaGalleryEntryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Api\AttributeValue
     */
    protected $attributeValueMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->contentValidatorMock = $this->createMock(\Magento\Framework\Api\ImageContentValidatorInterface::class);
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'setStoreId',
                'getData',
                'getStoreId',
                'getSku',
                'getCustomAttribute',
                'getMediaGalleryEntries',
                'setMediaGalleryEntries',
            ]
        );
        $this->mediaGalleryEntryMock =
            $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $this->model = new \Magento\Catalog\Model\Product\Gallery\GalleryManagement(
            $this->productRepositoryMock,
            $this->contentValidatorMock
        );
        $this->attributeValueMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     */
    public function testCreateWithInvalidImageException()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('The image content is invalid. Verify the content and try again.');

        $entryContentMock = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(false);

        $this->model->create("sku", $this->mediaGalleryEntryMock);
    }

    /**
     */
    public function testCreateWithCannotSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The product can\'t be saved.');

        $productSku = 'mediaProduct';
        $entryContentMock = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->create($productSku, $this->mediaGalleryEntryMock);
    }

    public function testCreate()
    {
        $productSku = 'mediaProduct';
        $entryContentMock = $this->createMock(
            \Magento\Framework\Api\Data\ImageContentInterface::class
        );
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);
        $this->productRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willReturn($this->productMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(true);

        $newEntryMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $newEntryMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->productMock->expects($this->at(2))->method('getMediaGalleryEntries')
            ->willReturn([$newEntryMock]);
        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$this->mediaGalleryEntryMock]);

        $this->assertEquals(42, $this->model->create($productSku, $this->mediaGalleryEntryMock));
    }

    /**
     */
    public function testUpdateWithNonExistingImage()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');

        $productSku = 'testProduct';
        $entryMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
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
     */
    public function testUpdateWithCannotSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('The product can\'t be saved.');

        $productSku = 'testProduct';
        $entryMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $existingEntryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getTypes')->willReturn([]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->update($productSku, $entryMock);
    }

    /**
     * Check update gallery entry behavior.
     *
     * @return void
     */
    public function testUpdate()
    {
        $productSku = 'testProduct';
        $entryMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $entrySecondId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingSecondEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
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

        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$entryMock, $existingSecondEntryMock])
            ->willReturnSelf();
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);

        $this->assertTrue($this->model->update($productSku, $entryMock));
    }

    /**
     */
    public function testRemoveWithNonExistingImage()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('No image with the provided ID was found. Verify the ID and try again.');

        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->remove($productSku, $entryId);
    }

    public function testRemove()
    {
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([]);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->model->remove($productSku, $entryId));
    }

    /**
     */
    public function testGetWithNonExistingProduct()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('The product doesn\'t exist. Verify and try again.');

        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willThrowException(new \Exception());
        $this->model->get($productSku, $imageId);
    }

    /**
     */
    public function testGetWithNonExistingImage()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('The image doesn\'t exist. Verify and try again.');

        $productSku = 'testProduct';
        $imageId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(44);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->model->get($productSku, $imageId);
    }

    public function testGet()
    {
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->createMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(42);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $this->assertEquals($existingEntryMock, $this->model->get($productSku, $imageId));
    }

    public function testGetList()
    {
        $productSku = 'testProductSku';
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $entryMock = $this->createMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $this->assertEquals([$entryMock], $this->model->getList($productSku));
    }
}
