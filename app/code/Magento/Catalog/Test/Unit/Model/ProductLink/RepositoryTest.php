<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ProductLink\CollectionProvider;
use Magento\Catalog\Model\ProductLink\Management;
use Magento\Catalog\Model\ProductLink\Repository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $hydratorPoolMock;

    /**
     * @var MockObject
     */
    protected $hydratorMock;

    /**
     * @var MockObject
     */
    protected $metadataMock;

    /**
     * @var MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var MockObject
     */
    protected $linkResourceMock;

    /**
     * @var Repository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $entityCollectionProviderMock;

    /**
     * @var MockObject
     */
    protected $linkInitializerMock;

    /**
     * Test method
     */
    protected function setUp(): void
    {
        $linkManagementMock = $this->createMock(Management::class);
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->entityCollectionProviderMock = $this->createMock(
            CollectionProvider::class
        );
        $this->linkInitializerMock = $this->createMock(
            ProductLinks::class
        );
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->hydratorPoolMock = $this->createMock(HydratorPool::class);
        $this->hydratorMock = $this->createPartialMock(Hydrator::class, ['extract']);
        $this->metadataMock = $this->createMock(EntityMetadata::class);
        $this->linkTypeProvider = $this->createMock(LinkTypeProvider::class);
        $this->linkResourceMock = $this->createMock(Link::class);
        $this->hydratorPoolMock->expects($this->any())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->metadataMock);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Repository::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'entityCollectionProvider' => $this->entityCollectionProviderMock,
                'linkInitializer' => $this->linkInitializerMock,
                'linkManagement' => $linkManagementMock,
                'metadataPool' => $this->metadataPoolMock,
                'hydratorPool' => $this->hydratorPoolMock,
                'linkTypeProvider' => $this->linkTypeProvider,
                'linkResource' => $this->linkResourceMock
            ]
        );
    }

    /**
     * Test method
     */
    public function testSave()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(Product::class);

        $linkedProductMock = $this->createMock(Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->willReturnMap([
            ['product', false, null, false, $productMock],
            ['linkedProduct', false, null, false, $linkedProductMock],
        ]);
        $entityMock->expects($this->any())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $this->linkResourceMock->expects($this->once())->method('saveProductLinks')->with($parentId, [
            $linkedProductId => ['product_id' => $linkedProductId]
        ], $typeId);
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn($linkedProductId);

        $this->assertTrue($this->model->save($entityMock));
    }

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The linked products data is invalid. Verify the data and try again.');
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(Product::class);
        $linkedProductMock = $this->createMock(Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->willReturnMap([
            ['product', false, null, false, $productMock],
            ['linkedProduct', false, null, false, $linkedProductMock],
        ]);
        $entityMock->expects($this->any())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $this->linkResourceMock->expects($this->once())->method('saveProductLinks')->with($parentId, [
            $linkedProductId => ['product_id' => $linkedProductId]
        ], $typeId)->willThrowException(new \Exception());
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn($linkedProductId);
        $this->model->save($entityMock);
    }

    public function testSaveWithoutLinkedProductSku()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The linked product SKU is invalid. Verify the data and try again.');
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $entityMock->expects($this->any())->method('getSku')->willReturn('sku1');
        $entityMock->expects($this->any())->method('getLinkedProductSku')->willReturn('');
        $this->model->save($entityMock);
    }

    public function testSaveWithoutProductSku()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage(
            'The parent product SKU is required for linking child products. '
            . 'Please ensure the parent product SKU is provided and try again.'
        );
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $entityMock->expects($this->any())->method('getSku')->willReturn('');
        $entityMock->expects($this->any())->method('getLinkedProductSku')->willReturn('linkedProductSku');
        $this->model->save($entityMock);
    }

    /**
     * Test method
     */
    public function testDelete()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(Product::class);
        $linkedProductMock = $this->createMock(Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->willReturnMap([
            ['product', false, null, false, $productMock],
            ['linkedProduct', false, null, false, $linkedProductMock],
        ]);
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $linkedProductMock->expects($this->once())->method('getId')->willReturn($linkedProductId);
        $this->linkResourceMock->expects($this->once())->method('getProductLinkId')
            ->with($parentId, $linkedProductId, $typeId)
            ->willReturn($linkId);
        $this->linkResourceMock->expects($this->once())->method('deleteProductLink')->with($linkId);

        $this->assertTrue($this->model->delete($entityMock));
    }

    public function testDeleteWithInvalidDataException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The linked products data is invalid. Verify the data and try again.');
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(Product::class);
        $linkedProductMock = $this->createMock(Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->willReturnMap([
            ['product', false, null, false, $productMock],
            ['linkedProduct', false, null, false, $linkedProductMock],
        ]);
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
        $entityMock->expects($this->exactly(1))->method('getLinkType')->willReturn('linkType');
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => $typeId]);
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')
            ->with($productMock)
            ->willReturn(['linkField' => $parentId]);
        $linkedProductMock->expects($this->once())->method('getId')->willReturn($linkedProductId);
        $this->linkResourceMock->expects($this->once())->method('getProductLinkId')
            ->with($parentId, $linkedProductId, $typeId)
            ->willReturn($linkId);
        $this->linkResourceMock->expects($this->once())->method('deleteProductLink')
            ->with($linkId)
            ->willThrowException(new \Exception());
        $this->model->delete($entityMock);
    }

    public function testDeleteWithNoSuchEntityException()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage(
            'Product with SKU \'linkedProduct\' is not linked to product with SKU \'product\''
        );

        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(Product::class);
        $linkedProductMock = $this->createMock(Product::class);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->willReturnMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        );
        $entityMock->expects($this->exactly(2))->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getSku')->willReturn('product');
        $entityMock->expects($this->once())->method('getLinkType')->willReturn('linkType');
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('linkField');
        $this->hydratorMock->expects($this->once())->method('extract')->willReturn(['linkField' => 'parent_id']);
        $this->linkTypeProvider->expects($this->once())->method('getLinkTypes')->willReturn(['linkType' => 1]);

        $this->model->delete($entityMock);
    }
}
