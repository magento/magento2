<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Link;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Downloadable\Model\Link\CreateHandler;
use Magento\Downloadable\Model\Product\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateHandlerTest extends TestCase
{
    /** @var CreateHandler */
    protected $model;

    /** @var LinkRepositoryInterface|MockObject */
    protected $linkRepositoryMock;

    protected function setUp(): void
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

        /** @var LinkInterface|MockObject $linkMock */
        $linkMock = $this->getMockBuilder(LinkInterface::class)
            ->getMock();
        $linkMock->expects($this->once())
            ->method('setId')
            ->with(null);

        /** @var ProductExtensionInterface|MockObject $productExtensionMock */
        $productExtensionMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->addMethods(['getDownloadableProductLinks'])
            ->getMockForAbstractClass();
        $productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$linkMock]);

        /** @var ProductInterface|MockObject $entityMock */
        $entityMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])
            ->onlyMethods(['getTypeId', 'getExtensionAttributes', 'getSku'])
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
        /** @var ProductInterface|MockObject $entityMock */
        $entityMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])
            ->onlyMethods(['getTypeId', 'getExtensionAttributes', 'getSku'])
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
