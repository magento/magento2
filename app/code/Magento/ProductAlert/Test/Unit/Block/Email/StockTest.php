<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Block\Email;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Block\Email\Stock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Stock
 */
class StockTest extends TestCase
{
    /**
     * @var MockObject|Stock
     */
    protected $_block;

    /**
     * @var MockObject|MaliciousCode
     */
    protected $_filter;

    /**
     * @var ImageBuilder|MockObject
     */
    protected $imageBuilder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_filter = $this->createPartialMock(MaliciousCode::class, ['filter']);

        $this->imageBuilder = $this->getMockBuilder(ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_block = $objectManager->getObject(
            Stock::class,
            [
                'maliciousCode' => $this->_filter,
                'imageBuilder' => $this->imageBuilder
            ]
        );
    }

    /**
     * @dataProvider getFilteredContentDataProvider
     * @param $contentToFilter
     * @param $contentFiltered
     */
    public function testGetFilteredContent($contentToFilter, $contentFiltered)
    {
        $this->_filter->expects($this->once())->method('filter')->with($contentToFilter)
            ->willReturn($contentFiltered);
        $this->assertEquals($contentFiltered, $this->_block->getFilteredContent($contentToFilter));
    }

    /**
     * @return array
     */
    public function getFilteredContentDataProvider()
    {
        return [
            'normal desc' => ['<b>Howdy!</b>', '<b>Howdy!</b>'],
            'malicious desc 1' => ['<javascript>Howdy!</javascript>', 'Howdy!'],
        ];
    }

    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productImageMock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilder->expects($this->atLeastOnce())->method('create')->willReturn($productImageMock);
        $this->assertInstanceOf(
            Image::class,
            $this->_block->getImage($productMock, $imageId, $attributes)
        );
    }
}
