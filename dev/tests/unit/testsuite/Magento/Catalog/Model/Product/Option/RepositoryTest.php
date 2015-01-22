<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Repository
     */
    protected $optionRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Model\ProductRepository', [], [], '', false);
        $this->optionResourceMock = $this->getMock('Magento\Catalog\Model\Resource\Product\Option', [], [], '', false);
        $this->optionMock = $this->getMock('\Magento\Catalog\Model\Product\Option', [], [], '', false);
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->converterMock = $this->getMock('\Magento\Catalog\Model\Product\Option\Converter', [], [], '', false);
        $this->optionRepository = new Repository(
            $this->productRepositoryMock,
            $this->optionResourceMock,
            $this->converterMock
        );
    }

    public function testGetList()
    {
        $productSku = 'simple_product';
        $expectedResult = ['Expected_option'];
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->optionRepository->getList($productSku));
    }

    public function testDelete()
    {
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->assertTrue($this->optionRepository->delete($this->optionMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetNonExistingOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId);
        $this->optionRepository->get($productSku, $optionId);
    }

    public function testGet()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, false)
            ->willReturn($this->productMock);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)->willReturn($this->optionMock);
        $this->assertEquals($this->optionMock, $this->optionRepository->get($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteByIdentifierNonExistingOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn(null);
        $this->optionRepository->deleteByIdentifier($productSku, $optionId);
    }

    public function testDeleteByIdentifier()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testDeleteByIdentifierWhenCannotRemoveOption()
    {
        $optionId = 1;
        $productSku = 'simple_product';
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())->method('getOptions')->willReturn([$optionId => $this->optionMock]);
        $this->productMock
            ->expects($this->once())
            ->method('getOptionById')
            ->with($optionId)
            ->willReturn($this->optionMock);
        $this->optionResourceMock->expects($this->once())->method('delete')->with($this->optionMock);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->productMock)
            ->willThrowException(new \Exception());
        $this->assertTrue($this->optionRepository->deleteByIdentifier($productSku, $optionId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testUpdateNonExistingOption()
    {
        $convertedOption = [
            'title' => 'test_option_code_1',
            'type' => 'field',
            'sort_order' => 1,
            'is_require' => 1,
            'price' => 10,
            'price_type' => 'fixed',
            'sku' => 'sku1',
            'max_characters' => 10,
            'values' => [],
        ];
        $optionId = 10;
        $productSku = 'productSku';
        $this->optionMock->expects($this->once())->method('getProductSku')->willReturn($productSku);
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($productSku, true)->willReturn($this->productMock);
        $this->converterMock
            ->expects($this->once())
            ->method('toArray')
            ->with($this->optionMock)
            ->will($this->returnValue($convertedOption));
        $this->optionMock
            ->expects($this->any())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->productMock->expects($this->once())->method('getOptionById')->with($optionId);
        $this->optionRepository->save($this->optionMock);
    }
}
