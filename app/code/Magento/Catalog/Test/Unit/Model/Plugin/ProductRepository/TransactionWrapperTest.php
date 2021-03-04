<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Plugin\ProductRepository;

class TransactionWrapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Plugin\ProductRepository\TransactionWrapper
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Api\ProductRepositoryInterface
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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var bool
     */
    protected $saveOption = true;

    const ERROR_MSG = "error occurred";

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock = $this->productMock;
        $this->closureMock = function () use ($productMock) {
            return $productMock;
        };
        $this->rollbackClosureMock = function () use ($productMock) {
            throw new \Exception(self::ERROR_MSG);
        };

        $this->model = new \Magento\Catalog\Model\Plugin\ProductRepository\TransactionWrapper($this->resourceMock);
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

    /**
     */
    public function testAroundSaveRollBack()
    {
        $this->expectException(\Exception::class);
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

    /**
     */
    public function testAroundDeleteRollBack()
    {
        $this->expectException(\Exception::class);
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

    /**
     */
    public function testAroundDeleteByIdRollBack()
    {
        $this->expectException(\Exception::class);
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
