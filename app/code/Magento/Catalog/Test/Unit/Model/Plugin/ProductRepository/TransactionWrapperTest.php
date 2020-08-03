<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Plugin\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Plugin\ProductRepository\TransactionWrapper;
use Magento\Catalog\Model\ResourceModel\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionWrapperTest extends TestCase
{
    /**
     * @var TransactionWrapper
     */
    protected $model;

    /**
     * @var MockObject|Product
     */
    protected $resourceMock;

    /**
     * @var MockObject|ProductRepositoryInterface
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \Closure
     */
    protected $rollbackClosureMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var bool
     */
    protected $saveOption = true;

    const ERROR_MSG = "error occurred";

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(Product::class);
        $this->subjectMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock = $this->productMock;
        $this->closureMock = function () use ($productMock) {
            return $productMock;
        };
        $this->rollbackClosureMock = function () use ($productMock) {
            throw new \Exception(self::ERROR_MSG);
        };

        $this->model = new TransactionWrapper($this->resourceMock);
    }

    public function testAroundSaveCommit()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('commit');

        $this->assertEquals(
            $this->productMock,
            $this->model->aroundSave($this->subjectMock, $this->closureMock, $this->productMock, $this->saveOption)
        );
    }

    public function testAroundSaveRollBack()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('error occurred');
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('rollBack');

        $this->model->aroundSave($this->subjectMock, $this->rollbackClosureMock, $this->productMock, $this->saveOption);
    }

    public function testAroundDeleteCommit()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('commit');

        $this->assertEquals(
            $this->productMock,
            $this->model->aroundDelete($this->subjectMock, $this->closureMock, $this->productMock, $this->saveOption)
        );
    }

    public function testAroundDeleteRollBack()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('error occurred');
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('rollBack');

        $this->model->aroundDelete(
            $this->subjectMock,
            $this->rollbackClosureMock,
            $this->productMock,
            $this->saveOption
        );
    }

    public function testAroundDeleteByIdCommit()
    {
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('commit');

        $this->assertEquals(
            $this->productMock,
            $this->model->aroundDelete($this->subjectMock, $this->closureMock, $this->productMock, $this->saveOption)
        );
    }

    public function testAroundDeleteByIdRollBack()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('error occurred');
        $this->resourceMock->expects($this->once())->method('beginTransaction');
        $this->resourceMock->expects($this->once())->method('rollBack');

        $this->model->aroundDelete(
            $this->subjectMock,
            $this->rollbackClosureMock,
            $this->productMock,
            $this->saveOption
        );
    }
}
