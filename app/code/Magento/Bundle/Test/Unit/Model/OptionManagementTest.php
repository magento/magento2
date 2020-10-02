<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\ProductOptionRepositoryInterface;
use Magento\Bundle\Model\OptionManagement;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionManagementTest extends TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var MockObject
     */
    protected $optionMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $this->optionRepositoryMock =
            $this->getMockForAbstractClass(ProductOptionRepositoryInterface::class);
        $this->productRepositoryMock =
            $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->optionMock = $this->getMockForAbstractClass(OptionInterface::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->model = new OptionManagement($this->optionRepositoryMock, $this->productRepositoryMock);
    }

    public function testSave()
    {
        $this->optionMock->expects($this->once())->method('getSku')->willReturn('bundle_product_sku');
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with('bundle_product_sku')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->optionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->optionMock);

        $this->model->save($this->optionMock);
    }

    public function testSaveWithException()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('This is implemented for bundle products only.');

        $this->optionMock->expects($this->once())->method('getSku')->willReturn('bundle_product_sku');
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with('bundle_product_sku')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->optionRepositoryMock->expects($this->never())->method('save');

        $this->model->save($this->optionMock);
    }
}
