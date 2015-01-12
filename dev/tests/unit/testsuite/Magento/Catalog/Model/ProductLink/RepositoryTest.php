<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\ProductLink;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
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

    protected function setUp()
    {
        $linkManagementMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Management', [], [], '', false);
        $this->productRepositoryMock = $this->getMock('\Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->entityCollectionProviderMock = $this->getMock(
            '\Magento\Catalog\Model\ProductLink\CollectionProvider',
            [],
            [],
            '',
            false
        );
        $this->linkInitializerMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks',
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\ProductLink\Repository',
            [
                'productRepository' => $this->productRepositoryMock,
                'entityCollectionProvider' => $this->entityCollectionProviderMock,
                'linkInitializer' => $this->linkInitializerMock,
                'linkManagement' => $linkManagementMock
            ]
        );
    }

    public function testSave()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, $productMock],
                ['linkedProduct', false, null, $linkedProductMock],
            ]
        ));
        $customAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeInterface');
        $customAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attribute_code');
        $customAttributeMock->expects($this->once())->method('getValue')->willReturn('value');
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getProductSku')->willReturn('product');
        $entityMock->expects($this->exactly(2))->method('getLinkType')->willReturn('linkType');
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $entityMock->expects($this->once())->method('getCustomAttributes')->willReturn([$customAttributeMock]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->entityCollectionProviderMock->expects($this->once())->method('getCollection')->willReturn([]);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')->with($productMock, [
            'linkType' => [42 => ['attribute_code' => 'value', 'product_id' => 42]]
        ]);
        $this->assertTrue($this->model->save($entityMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testSaveWithException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, $productMock],
                ['linkedProduct', false, null, $linkedProductMock],
            ]
        ));
        $customAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeInterface');
        $customAttributeMock->expects($this->once())->method('getAttributeCode')->willReturn('attribute_code');
        $customAttributeMock->expects($this->once())->method('getValue')->willReturn('value');
        $entityMock->expects($this->once())->method('getCustomAttributes')->willReturn([$customAttributeMock]);
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getProductSku')->willReturn('product');
        $entityMock->expects($this->exactly(2))->method('getLinkType')->willReturn('linkType');
        $entityMock->expects($this->once())->method('__toArray')->willReturn([]);
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->entityCollectionProviderMock->expects($this->once())->method('getCollection')->willReturn([]);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')->with($productMock, [
            'linkType' => [42 => ['attribute_code' => 'value', 'product_id' => 42]]
        ]);
        $productMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->save($entityMock);
    }

    public function testDelete()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, $productMock],
                ['linkedProduct', false, null, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getProductSku')->willReturn('product');
        $entityMock->expects($this->exactly(2))->method('getLinkType')->willReturn('linkType');
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->entityCollectionProviderMock->expects($this->once())->method('getCollection')->willReturn([
            42 => '', 37 => '',
        ]);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')->with($productMock, [
            'linkType' => [37 => '']
        ]);
        $this->assertTrue($this->model->delete($entityMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid data provided for linked products
     */
    public function testDeleteWithInvalidDataException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, $productMock],
                ['linkedProduct', false, null, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->once())->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->once())->method('getProductSku')->willReturn('product');
        $entityMock->expects($this->exactly(2))->method('getLinkType')->willReturn('linkType');
        $linkedProductMock->expects($this->exactly(2))->method('getId')->willReturn(42);
        $this->entityCollectionProviderMock->expects($this->once())->method('getCollection')->willReturn([
            42 => '', 37 => '',
        ]);
        $this->linkInitializerMock->expects($this->once())->method('initializeLinks')->with($productMock, [
            'linkType' => [37 => '']
        ]);
        $productMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->model->delete($entityMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Product with SKU linkedProduct is not linked to product with SKU product
     */
    public function testDeleteWithNoSuchEntityException()
    {
        $entityMock = $this->getMock('\Magento\Catalog\Model\ProductLink\Link', [], [], '', false);
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $this->productRepositoryMock->expects($this->exactly(2))->method('get')->will($this->returnValueMap(
            [
                ['product', false, null, $productMock],
                ['linkedProduct', false, null, $linkedProductMock],
            ]
        ));
        $entityMock->expects($this->exactly(2))->method('getLinkedProductSku')->willReturn('linkedProduct');
        $entityMock->expects($this->exactly(2))->method('getProductSku')->willReturn('product');
        $entityMock->expects($this->once())->method('getLinkType')->willReturn('linkType');
        $this->model->delete($entityMock);
    }
}
