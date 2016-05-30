<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Block\Plugin\Product\Media;

/**
 * Class GalleryTest
 */
class GalleryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Block\Plugin\Product\Media\Gallery
     */
    private $plugin;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    private $galleryHandler;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->galleryHandler = $this->getMockBuilder('\Magento\Catalog\Model\Product\Gallery\ReadHandler')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->jsonEncoder = $this->getMock('\Magento\Framework\Json\EncoderInterface');
        $this->jsonDecoder = $this->getMock('\Magento\Framework\Json\DecoderInterface');

        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getTypeId', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $variationProduct = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['setMediaGalleryEntries', 'getSku', 'getMediaGalleryImages', 'getImage', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $image = new \Magento\Framework\DataObject(
            ['media_type' => 'type', 'video_url' => 'url', 'file' => 'image.jpg']
        );
        $variationProduct->expects($this->any())->method('setMediaGalleryEntries')->willReturn([]);
        $variationProduct->expects($this->any())->method('getSku')->willReturn('sku');
        $variationProduct->expects($this->any())->method('getMediaGalleryImages')->willReturn([$image]);
        $variationProduct->expects($this->any())->method('getImage')->willReturn('image.jpg');
        $variationProduct->expects($this->any())->method('getData')->with('configurable_attribute')->willReturn(1);

        $this->galleryHandler->expects($this->once())->method('execute')->with($variationProduct);

        $configurableType = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->setMethods(['getUsedProducts', 'getConfigurableAttributesAsArray'])
            ->getMock();
        $configurableType->expects($this->any())->method('getUsedProducts')->with($productMock)
            ->willReturn([$variationProduct]);
        $configurableType->expects($this->any())->method('getConfigurableAttributesAsArray')->with($productMock)
            ->willReturn([['attribute_code' => 'configurable_attribute']]);

        $productMock->expects($this->any())->method('getTypeId')->willReturn('configurable');
        $productMock->expects($this->any())->method('getTypeInstance')->willReturn($configurableType);

        $this->plugin = $helper->getObject(
            '\Magento\ConfigurableProduct\Block\Plugin\Product\Media\Gallery',
            [
                'productGalleryReadHandler' => $this->galleryHandler,
                'jsonEncoder' => $this->jsonEncoder,
                'jsonDecoder' => $this->jsonDecoder
            ]
        );
        $this->plugin->setData('product', $productMock);
    }

    public function testAfterGetOptions()
    {
        $resultJson = '[]';
        $this->jsonDecoder->expects($this->once())->method('decode')->with('[]')->willReturn([]);
        $expected = [
            'configurable_attribute_1' => [
                [
                    'mediaType' => 'type',
                    'videoUrl' => 'url',
                    'isBase' => true
                ]
            ]
        ];
        $this->jsonEncoder->expects($this->any())->method('encode')->with($expected)
            ->willReturn(json_encode($expected));

        $blockMock = $this->getMockBuilder('\Magento\ProductVideo\Block\Product\View\Gallery')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->plugin->afterGetOptionsMediaGalleryDataJson($blockMock, $resultJson);
        $this->assertEquals(json_encode($expected), $result);
    }
}
