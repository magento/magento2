<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model;

use \Magento\Bundle\Model\OptionManagement;

class OptionManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OptionManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->optionRepositoryMock =
            $this->createMock(\Magento\Bundle\Api\ProductOptionRepositoryInterface::class);
        $this->productRepositoryMock =
            $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->optionMock = $this->createMock(\Magento\Bundle\Api\Data\OptionInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);

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
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->optionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->optionMock);

        $this->model->save($this->optionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage This is implemented for bundle products only.
     */
    public function testSaveWithException()
    {
        $this->optionMock->expects($this->once())->method('getSku')->willReturn('bundle_product_sku');
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with('bundle_product_sku')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->optionRepositoryMock->expects($this->never())->method('save');

        $this->model->save($this->optionMock);
    }
}
