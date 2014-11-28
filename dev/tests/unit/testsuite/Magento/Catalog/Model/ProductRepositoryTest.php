<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model;

use Magento\TestFramework\Helper\ObjectManager;

class ProductRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $initializationHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var array data to create product
     */
    protected $productData = [
        'sku' => 'exisiting',
        'name' => 'existing product',
    ];

    protected function setUp()
    {
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', ['create'], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->initializationHelperMock = $this->getMock(
            '\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->resourceModelMock = $this->getMock('\Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\ProductRepository',
            [
                'productFactory' => $this->productFactoryMock,
                'initializationHelper' => $this->initializationHelperMock,
                'resourceModel' => $this->resourceModelMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testGetAbsentProduct()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue(null));
        $this->productFactoryMock->expects($this->never())->method('setData');
        $this->model->get('test_sku');
    }

    public function testCreateCreatesProduct()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->assertEquals($this->productMock, $this->model->get('test_sku'));
    }

    public function testGetProductInEditMode()
    {
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with('test_sku')
            ->will($this->returnValue('test_id'));
        $this->productMock->expects($this->once())->method('setData')->with('_edit_mode', true);
        $this->productMock->expects($this->once())->method('load')->with('test_id');
        $this->assertEquals($this->productMock, $this->model->get('test_sku', true));
    }

    public function testSaveExisting()
    {
        $this->resourceModelMock->expects($this->exactly(2))->method('getIdBySku')->will($this->returnValue(100));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->once())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    public function testSaveNew()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)->willReturn(true);
        $this->assertEquals($this->productMock, $this->model->save($this->productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save product
     */
    public function testSaveUnableToSaveException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Exception);
        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the  field.
     */
    public function testSaveException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(true);
        $this->resourceModelMock->expects($this->once())->method('save')->with($this->productMock)
            ->willThrowException(new \Magento\Eav\Model\Entity\Attribute\Exception('123'));
        $this->productMock->expects($this->never())->method('getId');
        $this->model->save($this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Invalid product data: error1,error2
     */
    public function testSaveInvalidProductException()
    {
        $this->resourceModelMock->expects($this->exactly(1))->method('getIdBySku')->will($this->returnValue(null));
        $this->productMock->expects($this->once())->method('toFlatArray')->will($this->returnValue($this->productData));
        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->productMock));
        $this->initializationHelperMock->expects($this->never())->method('initialize')->with($this->productMock);
        $this->resourceModelMock->expects($this->once())->method('validate')->with($this->productMock)
            ->willReturn(['error1', 'error2']);
        $this->productMock->expects($this->never())->method('getId');
        $this->model->save($this->productMock);
    }

    public function testDelete()
    {
        $this->productMock->expects($this->once())->method('getSku')->willReturn('product-42');
        $this->resourceModelMock->expects($this->once())->method('delete')->with($this->productMock)
            ->willReturn(true);
        $this->assertTrue($this->model->delete($this->productMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Unable to remove product product-42
     */
    public function testDeleteException()
    {
        $this->productMock->expects($this->once())->method('getSku')->willReturn('product-42');
        $this->resourceModelMock->expects($this->once())->method('delete')->with($this->productMock)
            ->willThrowException(new \Exception);
        $this->model->delete($this->productMock);
    }

    public function testDeleteById()
    {
        $sku = 'product-42';
        $this->productFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->productMock));
        $this->resourceModelMock->expects($this->once())->method('getIdBySku')->with($sku)
            ->will($this->returnValue('42'));
        $this->productMock->expects($this->once())->method('load')->with('42');
        $this->assertTrue($this->model->deleteById($sku));
    }
}
