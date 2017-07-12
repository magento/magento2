<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Test\Unit\Block\Email;

/**
 * Test class for \Magento\ProductAlert\Block\Product\View\Stock
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\ProductAlert\Block\Email\Stock
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $_filter;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageBuilder;

    /**
     * @var \Magento\ProductAlert\Block\Product\ImageProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageProviderMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_filter = $this->getMock(
            \Magento\Framework\Filter\Input\MaliciousCode::class,
            ['filter'],
            [],
            '',
            false
        );

        $this->imageBuilder = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageProviderMock = $this->getMockBuilder(\Magento\ProductAlert\Block\Product\ImageProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_block = $objectManager->getObject(
            \Magento\ProductAlert\Block\Email\Stock::class,
            [
                'maliciousCode' => $this->_filter,
                'imageBuilder' => $this->imageBuilder,
                'imageProvider' => $this->imageProviderMock
            ]
        );
    }

    /**
     * @dataProvider testGetFilteredContentDataProvider
     * @param $contentToFilter
     * @param $contentFiltered
     */
    public function testGetFilteredContent($contentToFilter, $contentFiltered)
    {
        $this->_filter->expects($this->once())->method('filter')->with($contentToFilter)
            ->will($this->returnValue($contentFiltered));
        $this->assertEquals($contentFiltered, $this->_block->getFilteredContent($contentToFilter));
    }

    public function testGetFilteredContentDataProvider()
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

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productImageMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageProviderMock->expects($this->atLeastOnce())->method('getImage')->willReturn($productImageMock);

        $this->assertInstanceOf(
            \Magento\Catalog\Block\Product\Image::class,
            $this->_block->getImage($productMock, $imageId, $attributes)
        );
    }
}
