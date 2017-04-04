<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

class GalleryManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\GalleryManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryEntryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\AttributeValue
     */
    protected $attributeValueMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->contentValidatorMock = $this->getMock(\Magento\Framework\Api\ImageContentValidatorInterface::class);
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            [
                'setStoreId',
                'getData',
                'getStoreId',
                'getSku',
                'getCustomAttribute',
                'getMediaGalleryEntries',
                'setMediaGalleryEntries',
            ],
            [],
            '',
            false
        );
        $this->mediaGalleryEntryMock =
            $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $this->model = new \Magento\Catalog\Model\Product\Gallery\GalleryManagement(
            $this->productRepositoryMock,
            $this->contentValidatorMock
        );
        $this->attributeValueMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The image content is not valid.
     */
    public function testCreateWithInvalidImageException()
    {
        $entryContentMock = $this->getMockBuilder(\Magento\Framework\Api\Data\ImageContentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mediaGalleryEntryMock->expects($this->any())->method('getContent')->willReturn($entryContentMock);

        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->willReturn(false);

        $this->model->create("sku", $this->mediaGalleryEntryMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save product.
     */
    public function testCreateWithCannotSaveException()
    {
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
        $entryContentMock = $this->getMock(
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

        $newEntryMock = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $newEntryMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->productMock->expects($this->at(2))->method('getMediaGalleryEntries')
            ->willReturn([$newEntryMock]);
        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$this->mediaGalleryEntryMock]);

        $this->assertEquals(42, $this->model->create($productSku, $this->mediaGalleryEntryMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testUpdateWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn(43);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->model->update($productSku, $entryMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save product.
     */
    public function testUpdateWithCannotSaveException()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingEntryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$existingEntryMock]);
        $entryMock->expects($this->once())->method('getId')->willReturn($entryId);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->model->update($productSku, $entryMock);
    }

    public function testUpdate()
    {
        $productSku = 'testProduct';
        $entryMock = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $entryId = 42;
        $entrySecondId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock(
            \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class
        );
        $existingSecondEntryMock = $this->getMock(
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
        $entryMock->expects($this->once())->method('getFile')->willReturn("base64");
        $entryMock->expects($this->once())->method('setId')->with(null);
        $entryMock->expects($this->exactly(2))->method('getTypes')->willReturn(['image']);

        $this->productMock->expects($this->once())->method('setMediaGalleryEntries')
            ->with([$entryMock, $existingSecondEntryMock])
            ->willReturnSelf();
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->model->update($productSku, $entryMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no image with provided ID.
     */
    public function testRemoveWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $entryId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock(
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
        $existingEntryMock = $this->getMock(
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Such product doesn't exist
     */
    public function testGetWithNonExistingProduct()
    {
        $productSku = 'testProduct';
        $imageId = 42;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willThrowException(new \Exception());
        $this->model->get($productSku, $imageId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionText Such image doesn't exist
     */
    public function testGetWithNonExistingImage()
    {
        $productSku = 'testProduct';
        $imageId = 43;
        $this->productRepositoryMock->expects($this->once())->method('get')->with($productSku)
            ->willReturn($this->productMock);
        $existingEntryMock = $this->getMock(
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
        $existingEntryMock = $this->getMock(
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
        $entryMock = $this->getMock(\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface::class);
        $this->productMock->expects($this->once())->method('getMediaGalleryEntries')
            ->willReturn([$entryMock]);
        $this->assertEquals([$entryMock], $this->model->getList($productSku));
    }
}
