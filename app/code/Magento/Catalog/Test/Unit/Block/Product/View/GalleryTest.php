<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

class GalleryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\View\Gallery
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Block\Product\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayUtils;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    protected function setUp()
    {
        $this->mockContext();

        $this->arrayUtils = $this->getMockBuilder(\Magento\Framework\Stdlib\ArrayUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\Catalog\Block\Product\View\Gallery(
            $this->context,
            $this->arrayUtils,
            $this->jsonEncoderMock
        );
    }

    protected function mockContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getImageHelper')
            ->willReturn($this->imageHelper);

        $this->registry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRegistry')
            ->willReturn($this->registry);
    }

    public function testGetGalleryImages()
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->once())
            ->method('getStoreFilter')
            ->with($productMock)
            ->willReturn($storeMock);

        $productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productMock->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn($this->getImagesCollection());

        $this->registry->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);

        $this->imageHelper->expects($this->exactly(3))
            ->method('init')
            ->willReturnMap([
                [$productMock, 'product_page_image_small', [], $this->imageHelper],
                [$productMock, 'product_page_image_medium_no_frame', [], $this->imageHelper],
                [$productMock, 'product_page_image_large_no_frame', [], $this->imageHelper],
            ])
            ->willReturnSelf();
        $this->imageHelper->expects($this->exactly(3))
            ->method('setImageFile')
            ->with('test_file')
            ->willReturnSelf();
        $this->imageHelper->expects($this->at(0))
            ->method('getUrl')
            ->willReturn('product_page_image_small_url');
        $this->imageHelper->expects($this->at(1))
            ->method('getUrl')
            ->willReturn('product_page_image_medium_url');
        $this->imageHelper->expects($this->at(2))
            ->method('getUrl')
            ->willReturn('product_page_image_large_url');

        $images = $this->model->getGalleryImages();
        $this->assertInstanceOf(\Magento\Framework\Data\Collection::class, $images);
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getImagesCollection()
    {
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            new \Magento\Framework\DataObject([
                'file' => 'test_file'
            ]),
        ];

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }
}
