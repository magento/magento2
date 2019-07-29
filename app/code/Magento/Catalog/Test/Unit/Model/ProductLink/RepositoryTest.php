<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $hydratorPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $hydratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkResourceMock;

    /**
     * @var \Magento\Catalog\Model\ProductLink\Repository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */

    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityCollectionProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkInitializerMock;

    /**
     * Test method
     */
    protected function setUp()
    {
        $linkManagementMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Management::class);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->entityCollectionProviderMock = $this->createMock(
            \Magento\Catalog\Model\ProductLink\CollectionProvider::class
        );
        $this->linkInitializerMock = $this->createMock(
            \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks::class
        );
        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->hydratorPoolMock = $this->createMock(\Magento\Framework\EntityManager\HydratorPool::class);
        $this->hydratorMock = $this->createPartialMock(\Magento\Framework\EntityManager\Hydrator::class, ['extract']);
        $this->metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);
        $this->linkTypeProvider = $this->createMock(\Magento\Catalog\Model\Product\LinkTypeProvider::class);
        $this->linkResourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Link::class);
        $this->hydratorPoolMock->expects($this->any())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->metadataMock);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\ProductLink\Repository::class,
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
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $linkedProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSaveWithException()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $linkedProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getSku')->willReturn('product');
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

    /**
     * Test method
     */
    public function testDelete()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $linkedProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testDeleteWithInvalidDataException()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $linkedProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $parentId = 42;
        $linkedProductId = 37;
        $typeId = 4;
        $linkId = 33;
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
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

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU 'linkedProduct' is not linked to product with SKU 'product'
     */
    public function testDeleteWithNoSuchEntityException()
    {
        $entityMock = $this->createMock(\Magento\Catalog\Model\ProductLink\Link::class);
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $linkedProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, false, $productMock],
                ['linkedProduct', false, null, false, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->exactly(2))->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getSku')->willReturn('product');
        $entityMock->expects($this->once())->method('getLinkType')->willReturn('linkType');
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($this->hydratorMock);
        $this->model->delete($entityMock);
    }
}
