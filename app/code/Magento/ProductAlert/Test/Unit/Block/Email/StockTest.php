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
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Store\Model\App\Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulationMock;

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
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appEmulationMock = $this->getMockBuilder(\Magento\Store\Model\App\Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->_block = $objectManager->getObject(
            \Magento\ProductAlert\Block\Email\Stock::class,
            [
                'maliciousCode' => $this->_filter,
                'imageBuilder' => $this->imageBuilder,
                'context' => $contextMock,
                'appEmulation' => $this->appEmulationMock
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

        $imageMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulationMock->expects($this->once())->method('startEnvironmentEmulation');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(42);
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
        $this->appEmulationMock->expects($this->once())->method('stopEnvironmentEmulation');

        $this->assertInstanceOf(
            \Magento\Catalog\Block\Product\Image::class,
            $this->_block->getImage($productMock, $imageId, $attributes)
        );
    }

    /**
     * Test that app emulation stops when exception occurs.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Image Builder Exception
     */
    public function testGetImageThrowsAnException()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appEmulationMock->expects($this->once())->method('startEnvironmentEmulation');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(42);

        $this->imageBuilder->expects($this->once())
            ->method('setProduct')
            ->willThrowException(new \Exception("Image Builder Exception"));
        $this->appEmulationMock->expects($this->once())->method('stopEnvironmentEmulation');

        $this->_block->getImage($productMock, 1, []);
    }
}
