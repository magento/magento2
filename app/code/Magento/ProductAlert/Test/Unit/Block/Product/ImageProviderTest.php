<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Test\Unit\Block\Product;

class ImageProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $imageBuilderMock;

    /**
     * @var \Magento\ProductAlert\Block\Product\ImageProvider
     */
    private $model;

    protected function setUp(): void
    {
        $this->imageBuilderMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\ImageBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\ProductAlert\Block\Product\ImageProvider(
            $this->imageBuilderMock
        );
    }

    /**
     * Test that image is created successfully with app emulation enabled.
     */
    public function testGetImage()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $imageMock = $this->createMock(\Magento\Catalog\Block\Product\Image::class);
        $this->imageBuilderMock->expects($this->once())
            ->method('create')
            ->with($productMock, $imageId, $attributes)
            ->willReturn($imageMock);

        $this->assertEquals($imageMock, $this->model->getImage($productMock, $imageId, $attributes));
    }
}
