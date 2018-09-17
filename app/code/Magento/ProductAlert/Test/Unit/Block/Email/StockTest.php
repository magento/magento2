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

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_filter = $this->getMock(
            '\Magento\Framework\Filter\Input\MaliciousCode',
            ['filter'],
            [],
            '',
            false
        );

        $this->imageBuilder = $this->getMockBuilder('Magento\Catalog\Block\Product\ImageBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_block = $objectManager->getObject(
            'Magento\ProductAlert\Block\Email\Stock',
            [
                'maliciousCode' => $this->_filter,
                'imageBuilder' => $this->imageBuilder,
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

    /**
     * @return array
     */
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

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $imageMock = $this->getMockBuilder('Magento\Catalog\Block\Product\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageBuilder->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setImageId')
            ->with($imageId)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('setAttributes')
            ->with($attributes)
            ->willReturnSelf();
        $this->imageBuilder->expects($this->once())
            ->method('create')
            ->willReturn($imageMock);

        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\Image',
            $this->_block->getImage($productMock, $imageId, $attributes)
        );
    }
}
