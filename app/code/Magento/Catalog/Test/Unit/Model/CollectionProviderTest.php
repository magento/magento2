<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\ProductLink\CollectionProvider;
use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;
use Magento\Catalog\Model\ProductLink\Converter\ConverterInterface;
use Magento\Catalog\Model\ProductLink\Converter\ConverterPool;
use Magento\Catalog\Model\Product;

class CollectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $converterPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $providerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $converterMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(Product::class, [], [], '', false, false);
        $this->converterPoolMock = $this->getMock(ConverterPool::class, [], [], '', false, false);
        $this->providerMock = $this->getMock(CollectionProviderInterface::class);
        $this->converterMock = $this->getMock(ConverterInterface::class);

        $this->model = new CollectionProvider($this->converterPoolMock, ['crosssell' => $this->providerMock]);
    }

    /**
     * Test sort order of linked products based on configured item position.
     */
    public function testGetCollection()
    {
        $linkedProductOneMock = $this->getMock(Product::class, [], [], '', false, false);
        $linkedProductTwoMock = $this->getMock(Product::class, [], [], '', false, false);
        $linkedProductThreeMock = $this->getMock(Product::class, [], [], '', false, false);

        $linkedProductOneMock->expects($this->once())->method('getId')->willReturn(1);
        $linkedProductTwoMock->expects($this->once())->method('getId')->willReturn(2);
        $linkedProductThreeMock->expects($this->once())->method('getId')->willReturn(3);

        $this->converterPoolMock->expects($this->once())
            ->method('getConverter')
            ->with('crosssell')
            ->willReturn($this->converterMock);

        $map = [
            [$linkedProductOneMock, ['name' => 'Product One', 'position' => 10]],
            [$linkedProductTwoMock, ['name' => 'Product Two', 'position' => 2]],
            [$linkedProductThreeMock, ['name' => 'Product Three', 'position' => 2]],
        ];

        $this->converterMock->expects($this->exactly(3))->method('convert')->willReturnMap($map);

        $this->providerMock->expects($this->once())
            ->method('getLinkedProducts')
            ->with($this->productMock)
            ->willReturn(
                [
                    $linkedProductOneMock,
                    $linkedProductTwoMock,
                    $linkedProductThreeMock
                ]
            );

        $expectedResult = [
            2 => ['name' => 'Product Two', 'position' => 2],
            3 => ['name' => 'Product Three', 'position' => 2],
            10 => ['name' => 'Product One', 'position' => 10],
        ];

        $actualResult = $this->model->getCollection($this->productMock, 'crosssell');

        $this->assertEquals($expectedResult, $actualResult, 'Sort order of linked products in incorrect');
    }

    /**
     * Test exception when collection provider is not configured for product link type.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Collection provider is not registered
     */
    public function testGetCollectionWithMissingProviders()
    {
        $this->model->getCollection($this->productMock, 'upsell');
    }
}
