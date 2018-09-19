<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Downloadable\Model\Link\CreateHandler;
use Magento\Downloadable\Model\Product\Type;

class CreateHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CreateHandler */
    protected $model;

    /** @var LinkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $linkRepositoryMock;

    protected function setUp()
    {
        $this->linkRepositoryMock = $this->getMockBuilder(LinkRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = new CreateHandler(
            $this->linkRepositoryMock
        );
    }

    public function testExecute()
    {
        $entitySku = 'sku';
        $entityStoreId = 0;

        /** @var LinkInterface|\PHPUnit_Framework_MockObject_MockObject $linkMock */
        $linkMock = $this->getMockBuilder(LinkInterface::class)
            ->getMock();
        $linkMock->expects($this->once())
            ->method('setId')
            ->with(null);

        /** @var ProductExtensionInterface|\PHPUnit_Framework_MockObject_MockObject $productExtensionMock */
        $productExtensionMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getDownloadableProductLinks'])
            ->getMockForAbstractClass();
        $productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$linkMock]);

        /** @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject $entityMock */
        $entityMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getTypeId', 'getExtensionAttributes', 'getSku', 'getStoreId'])
            ->getMockForAbstractClass();
        $entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE);
        $entityMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);
        $entityMock->expects($this->once())
            ->method('getSku')
            ->willReturn($entitySku);
        $entityMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($entityStoreId);

        $this->linkRepositoryMock->expects($this->never())
            ->method('getList');
        $this->linkRepositoryMock->expects($this->once())
            ->method('save')
            ->with($entitySku, $linkMock, !$entityStoreId);
        $this->linkRepositoryMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($entityMock, $this->model->execute($entityMock));
    }

    public function testExecuteNonDownloadable()
    {
        /** @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject $entityMock */
        $entityMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getTypeId', 'getExtensionAttributes', 'getSku', 'getStoreId'])
            ->getMockForAbstractClass();
        $entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE . 'some');
        $entityMock->expects($this->never())
            ->method('getExtensionAttributes');
        $entityMock->expects($this->never())
            ->method('getSku');
        $entityMock->expects($this->never())
            ->method('getStoreId');

        $this->linkRepositoryMock->expects($this->never())
            ->method('getList');
        $this->linkRepositoryMock->expects($this->never())
            ->method('save');
        $this->linkRepositoryMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($entityMock, $this->model->execute($entityMock));
    }
}
