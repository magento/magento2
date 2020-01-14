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

class CollectionProviderTest extends \PHPUnit\Framework\TestCase
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
        $this->productMock = $this->createMock(Product::class);
        $this->converterPoolMock = $this->createMock(ConverterPool::class);
        $this->providerMock = $this->createMock(CollectionProviderInterface::class);
        $this->converterMock = $this->createMock(ConverterInterface::class);

        $this->model = new CollectionProvider($this->converterPoolMock, ['crosssell' => $this->providerMock]);
    }

    /**
     * Test sort order of linked products based on configured item position.
     */
    public function testGetCollection()
    {
        $linkedProductOneMock = $this->createMock(Product::class);
        $linkedProductTwoMock = $this->createMock(Product::class);
        $linkedProductThreeMock = $this->createMock(Product::class);
        $linkedProductFourMock = $this->createMock(Product::class);
        $linkedProductFiveMock = $this->createMock(Product::class);

        $linkedProductOneMock->expects($this->once())->method('getId')->willReturn(1);
        $linkedProductTwoMock->expects($this->once())->method('getId')->willReturn(2);
        $linkedProductThreeMock->expects($this->once())->method('getId')->willReturn(3);
        $linkedProductFourMock->expects($this->once())->method('getId')->willReturn(4);
        $linkedProductFiveMock->expects($this->once())->method('getId')->willReturn(5);

        $this->converterPoolMock->expects($this->once())
            ->method('getConverter')
            ->with('crosssell')
            ->willReturn($this->converterMock);

        $map = [
            [$linkedProductOneMock, ['name' => 'Product One', 'position' => 10]],
            [$linkedProductTwoMock, ['name' => 'Product Two', 'position' => 2]],
            [$linkedProductThreeMock, ['name' => 'Product Three', 'position' => 2]],
            [$linkedProductFourMock, ['name' => 'Product Four', 'position' => null]],
            [$linkedProductFiveMock, ['name' => 'Product Five']],
        ];

        $this->converterMock->expects($this->exactly(5))->method('convert')->willReturnMap($map);

        $this->providerMock->expects($this->once())
            ->method('getLinkedProducts')
            ->with($this->productMock)
            ->willReturn(
                [
                    $linkedProductOneMock,
                    $linkedProductTwoMock,
                    $linkedProductThreeMock,
                    $linkedProductFourMock,
                    $linkedProductFiveMock,
                ]
            );

        $expectedResult = [
            0 => ['name' => 'Product Four', 'position' => 0, 'link_type' => 'crosssell'],
            1 => ['name' => 'Product Five', 'position' => 0, 'link_type' => 'crosssell'],
            2 => ['name' => 'Product Three', 'position' => 2, 'link_type' => 'crosssell'],
            3 => ['name' => 'Product Two', 'position' => 2, 'link_type' => 'crosssell'],
            4 => ['name' => 'Product One', 'position' => 10, 'link_type' => 'crosssell'],
        ];

        $actualResult = $this->model->getCollection($this->productMock, 'crosssell');

        $this->assertEquals($expectedResult, $actualResult, 'Sort order of linked products in incorrect');
    }

    /**
     * Test exception when collection provider is not configured for product link type.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage The collection provider isn't registered.
     */
    public function testGetCollectionWithMissingProviders()
    {
        $this->model->getCollection($this->productMock, 'upsell');
    }
}
