<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Block\Plugin\Product\Media;

use Magento\ConfigurableProduct\Block\Plugin\Product\Media\Gallery;
use Magento\Catalog\Model\Product;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class GalleryTest
 */
class GalleryTest extends \PHPUnit\Framework\TestCase
{

    public function testAfterGetOptions()
    {
        $jsonMock = $this->createJsonMock();
        $productMock = $this->createProductMock();
        $galleryMock = $this->createGalleryMock();
        $variationProductMock = $this->createProductMock();
        $configurableTypeMock = $this->createConfigurableTypeMock();

        $resultJson = '[]';
        $variationProductId = 1;
        $expectedGalleryJson = [
            $variationProductId => [
                [
                    'mediaType' => 'type',
                    'videoUrl' => 'url',
                    'isBase' => true
                ]
            ]
        ];
        $image = new \Magento\Framework\DataObject(
            ['media_type' => 'type', 'video_url' => 'url', 'file' => 'image.jpg']
        );

        $galleryMock->expects(($this->any()))->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('configurable');
        $productMock->expects($this->once())->method('getTypeInstance')->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->once())->method('getUsedProducts')->with($productMock)
            ->willReturn([$variationProductMock]);
        $variationProductMock->expects($this->once())->method('getId')->willReturn($variationProductId);
        $variationProductMock->expects($this->once())->method('getMediaGalleryImages')->willReturn([$image]);
        $variationProductMock->expects($this->once())->method('getImage')->willReturn('image.jpg');
        $jsonMock->expects($this->once())->method('serialize')->with($expectedGalleryJson)
            ->willReturn(json_encode($expectedGalleryJson));

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $plugin = $helper->getObject(
            Gallery::class,
            [
                'json' => $jsonMock
            ]
        );
        $result = $plugin->afterGetOptionsMediaGalleryDataJson($galleryMock, $resultJson);
        $this->assertEquals(json_encode($expectedGalleryJson), $result);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createJsonMock()
    {
        return $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createProductMock()
    {
        return $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createGalleryMock()
    {
        return $this->getMockBuilder(\Magento\Catalog\Block\Product\View\Gallery::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createConfigurableTypeMock()
    {
        return $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
