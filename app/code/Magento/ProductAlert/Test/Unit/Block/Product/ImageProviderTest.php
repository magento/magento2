<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Test\Unit\Block\Product;

class ImageProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageBuilderMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Store\Model\App\Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emulationMock;

    /**
     * @var \Magento\ProductAlert\Block\Product\ImageProvider
     */
    private $model;

    protected function setUp()
    {
        $this->imageBuilderMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emulationMock = $this->getMockBuilder(\Magento\Store\Model\App\Emulation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\ProductAlert\Block\Product\ImageProvider(
            $this->imageBuilderMock,
            $this->storeManagerMock,
            $this->emulationMock
        );
    }

    /**
     * Test that image is created successfully with app emulation enabled.
     */
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

        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(42);
        $this->emulationMock->expects($this->once())->method('startEnvironmentEmulation');
        $this->imageBuilderMock->expects($this->once())->method('setProduct')->with($productMock)->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())->method('setImageId')->with($imageId)->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())->method('setAttributes')->with($attributes)->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())->method('create')->willReturn($imageMock);
        $this->emulationMock->expects($this->once())->method('stopEnvironmentEmulation');

        $this->assertEquals($imageMock, $this->model->getImage($productMock, $imageId, $attributes));
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

        $this->emulationMock->expects($this->once())->method('startEnvironmentEmulation');
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->atLeastOnce())->method('getId')->willReturn(42);

        $this->imageBuilderMock->expects($this->once())
            ->method('setProduct')
            ->willThrowException(new \Exception("Image Builder Exception"));

        $this->emulationMock->expects($this->once())->method('stopEnvironmentEmulation');
        $this->model->getImage($productMock, 1);
    }
}
