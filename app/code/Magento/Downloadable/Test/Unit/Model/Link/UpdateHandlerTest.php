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
use Magento\Downloadable\Model\Link\UpdateHandler;
use Magento\Downloadable\Model\Product\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Model\Link\UpdateHandler.
 */
class UpdateHandlerTest extends TestCase
{
    /**
     * @var UpdateHandler
     */
    private $model;

    /**
     * @var LinkRepositoryInterface|MockObject
     */
    private $linkRepositoryMock;

    /**
     * @var LinkInterface|MockObject
     */
    private $linkMock;

    /**
     * @var ProductExtensionInterface|MockObject
     */
    private $productExtensionMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $entityMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->linkRepositoryMock = $this->getMockBuilder(LinkRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->linkMock = $this->getMockBuilder(LinkInterface::class)
            ->getMock();
        $this->productExtensionMock = $this->getProductExtensionMock();
        $this->productExtensionMock->expects($this->once())
            ->method('getDownloadableProductLinks')
            ->willReturn([$this->linkMock]);
        $this->entityMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();

        $this->model = new UpdateHandler(
            $this->linkRepositoryMock
        );
    }

    /**
     * Update links for downloadable product
     *
     * @return void
     */
    public function testExecute(): void
    {
        $entitySku = 'sku';
        $entityStoreId = 0;
        $linkToDeleteId = 22;

        $this->linkMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(1);

        /** @var LinkInterface|MockObject $linkToDeleteMock */
        $linkToDeleteMock = $this->getMockBuilder(LinkInterface::class)
            ->getMock();
        $linkToDeleteMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($linkToDeleteId);

        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE);
        $this->entityMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->entityMock->expects($this->exactly(2))
            ->method('getSku')
            ->willReturn($entitySku);
        $this->entityMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($entityStoreId);

        $this->linkRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($entitySku)
            ->willReturn([$this->linkMock, $linkToDeleteMock]);
        $this->linkRepositoryMock->expects($this->once())
            ->method('save')
            ->with($entitySku, $this->linkMock, !$entityStoreId);
        $this->linkRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($linkToDeleteId);

        $this->assertEquals($this->entityMock, $this->model->execute($this->entityMock));
    }

    /**
     * Update links for non downloadable product.
     *
     * @return void
     */
    public function testExecuteNonDownloadable(): void
    {
        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE . 'some');
        $this->entityMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->entityMock->expects($this->never())
            ->method('getSku');
        $this->entityMock->expects($this->never())
            ->method('getStoreId');

        $this->linkRepositoryMock->expects($this->never())
            ->method('getList');
        $this->linkRepositoryMock->expects($this->never())
            ->method('save');
        $this->linkRepositoryMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($this->entityMock, $this->model->execute($this->entityMock));
    }

    /**
     * Build product extension mock.
     *
     * @return MockObject
     */
    private function getProductExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor();
        try {
            $mockBuilder->addMethods(['getDownloadableProductLinks']);
        } catch (RuntimeException $e) {
            // ProductExtension already generated.
        }

        return $mockBuilder->getMockForAbstractClass();
    }
}
