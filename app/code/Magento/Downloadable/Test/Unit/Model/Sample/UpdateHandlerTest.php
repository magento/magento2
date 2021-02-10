<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sample;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Api\SampleRepositoryInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample\UpdateHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Model\Sample\UpdateHandler.
 */
class UpdateHandlerTest extends TestCase
{
    /**
     * @var UpdateHandler
     */
    private $model;

    /**
     * @var SampleRepositoryInterface|MockObject
     */
    private $sampleRepositoryMock;

    /**
     * @var SampleInterface|MockObject
     */
    private $sampleMock;

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
        $this->sampleRepositoryMock = $this->getMockBuilder(SampleRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->sampleMock = $this->getMockBuilder(SampleInterface::class)
            ->getMock();
        $this->productExtensionMock = $this->createMock(ProductExtensionInterface::class);
        $this->productExtensionMock//->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$this->sampleMock]);
        $this->entityMock = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId'])
            ->getMockForAbstractClass();

        $this->model = new UpdateHandler(
            $this->sampleRepositoryMock
        );
    }

    /**
     * Update samples for downloadable product
     *
     * @return void
     */
    public function testExecute(): void
    {
        $entitySku = 'sku';
        $entityStoreId = 0;
        $sampleToDeleteId = 22;

        $this->sampleMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(1);

        /** @var SampleInterface|MockObject $sampleToDeleteMock */
        $sampleToDeleteMock = $this->getMockBuilder(SampleInterface::class)
            ->getMock();
        $sampleToDeleteMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($sampleToDeleteId);

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

        $this->sampleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($entitySku)
            ->willReturn([$this->sampleMock, $sampleToDeleteMock]);
        $this->sampleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($entitySku, $this->sampleMock, !$entityStoreId);
        $this->sampleRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($sampleToDeleteId);

        $this->assertEquals($this->entityMock, $this->model->execute($this->entityMock));
    }

    /**
     * Update samples for non downloadable product
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

        $this->sampleRepositoryMock->expects($this->never())
            ->method('getList');
        $this->sampleRepositoryMock->expects($this->never())
            ->method('save');
        $this->sampleRepositoryMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($this->entityMock, $this->model->execute($this->entityMock));
    }
}
