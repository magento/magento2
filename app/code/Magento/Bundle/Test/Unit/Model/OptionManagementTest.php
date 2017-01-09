<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model;

use \Magento\Bundle\Model\OptionManagement;

class OptionManagementTest extends \PHPUnit_Framework_TestCase
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
            $this->getMock(\Magento\Bundle\Api\ProductOptionRepositoryInterface::class, [], [], '', false);
        $this->productRepositoryMock =
            $this->getMock(\Magento\Catalog\Api\ProductRepositoryInterface::class, [], [], '', false);
        $this->optionMock = $this->getMock(\Magento\Bundle\Api\Data\OptionInterface::class, [], [], '', false);
        $this->productMock = $this->getMock(\Magento\Catalog\Api\Data\ProductInterface::class, [], [], '', false);

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
     * @expectedExceptionMessage Only implemented for bundle product
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
