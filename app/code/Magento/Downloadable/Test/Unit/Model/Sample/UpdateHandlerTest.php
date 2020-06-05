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

class UpdateHandlerTest extends TestCase
{
    /** @var UpdateHandler */
    protected $model;

    /** @var SampleRepositoryInterface|MockObject */
    protected $sampleRepositoryMock;

    protected function setUp(): void
    {
        $this->sampleRepositoryMock = $this->getMockBuilder(SampleRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = new UpdateHandler(
            $this->sampleRepositoryMock
        );
    }

    public function testExecute()
    {
        $entitySku = 'sku';
        $entityStoreId = 0;
        $sampleId = 11;
        $sampleToDeleteId = 22;

        /** @var SampleInterface|MockObject $sampleMock */
        $sampleMock = $this->getMockBuilder(SampleInterface::class)
            ->getMock();
        $sampleMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($sampleId);

        /** @var SampleInterface|MockObject $sampleToDeleteMock */
        $sampleToDeleteMock = $this->getMockBuilder(SampleInterface::class)
            ->getMock();
        $sampleToDeleteMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($sampleToDeleteId);

        /** @var ProductExtensionInterface|MockObject $productExtensionMock */
        $productExtensionMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->setMethods(['getDownloadableProductSamples'])
            ->getMockForAbstractClass();
        $productExtensionMock->expects($this->once())
            ->method('getDownloadableProductSamples')
            ->willReturn([$sampleMock]);

        /** @var ProductInterface|MockObject $entityMock */
        $entityMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getTypeId', 'getExtensionAttributes', 'getSku', 'getStoreId'])
            ->getMockForAbstractClass();
        $entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE);
        $entityMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);
        $entityMock->expects($this->exactly(2))
            ->method('getSku')
            ->willReturn($entitySku);
        $entityMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($entityStoreId);

        $this->sampleRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($entitySku)
            ->willReturn([$sampleMock, $sampleToDeleteMock]);
        $this->sampleRepositoryMock->expects($this->once())
            ->method('save')
            ->with($entitySku, $sampleMock, !$entityStoreId);
        $this->sampleRepositoryMock->expects($this->once())
            ->method('delete')
            ->with($sampleToDeleteId);

        $this->assertEquals($entityMock, $this->model->execute($entityMock));
    }

    public function testExecuteNonDownloadable()
    {
        /** @var ProductInterface|MockObject $entityMock */
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

        $this->sampleRepositoryMock->expects($this->never())
            ->method('getList');
        $this->sampleRepositoryMock->expects($this->never())
            ->method('save');
        $this->sampleRepositoryMock->expects($this->never())
            ->method('delete');

        $this->assertEquals($entityMock, $this->model->execute($entityMock));
    }
}
