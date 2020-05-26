<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sample;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Api\SampleRepositoryInterface as SampleRepository;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\Sample\DeleteHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Downloadable\Model\Sample\DeleteHandler
 */
class DeleteHandlerTest extends TestCase
{
    const STUB_PRODUCT_TYPE = 'simple';
    const STUB_PRODUCT_SKU = 'sku';
    const STUB_SAMPLE_ID = 1;

    /**
     * @var ProductInterface|MockObject
     */
    private $entityMock;

    /**
     * @var SampleRepository|MockObject
     */
    private $sampleRepositoryMock;

    /**
     * @var DeleteHandler
     */
    private $deleteHandler;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->entityMock = $this->createMock(Product::class);
        $this->sampleRepositoryMock = $this->getMockBuilder(SampleRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'delete'])
            ->getMockForAbstractClass();

        $this->deleteHandler = (new ObjectManagerHelper($this))->getObject(
            DeleteHandler::class,
            ['sampleRepository' => $this->sampleRepositoryMock]
        );
    }

    /**
     * Test case when provided Product has type Downloadable.
     */
    public function testExecuteWithDownloadableProduct()
    {
        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_DOWNLOADABLE);
        $this->entityMock->expects($this->once())
            ->method('getSku')
            ->willReturn(self::STUB_PRODUCT_SKU);

        $sampleMock = $this->getMockForAbstractClass(SampleInterface::class);
        $sampleMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::STUB_SAMPLE_ID);

        $this->sampleRepositoryMock->expects($this->once())->method('delete');
        $this->sampleRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([$sampleMock]);

        $this->assertSame($this->entityMock, $this->deleteHandler->execute($this->entityMock));
    }

    /**
     * Test case when provided Product is not Downloadable.
     */
    public function testExecuteWithOtherProduct()
    {
        $this->entityMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(self::STUB_PRODUCT_TYPE);

        $this->sampleRepositoryMock->expects($this->never())->method('getList');
        $this->sampleRepositoryMock->expects($this->never())->method('delete');
        $this->assertSame($this->entityMock, $this->deleteHandler->execute($this->entityMock));
    }
}
