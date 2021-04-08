<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Plugin\Product\Media;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Plugin\Product\Media\Gallery;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
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
        $image = new DataObject(
            ['media_type' => 'type', 'video_url' => 'url', 'file' => 'image.jpg']
        );

        $dataCollection = $this->getMockBuilder(DataCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMock();
        $galleryMock->expects(($this->any()))->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getTypeId')->willReturn('configurable');
        $productMock->expects($this->once())->method('getTypeInstance')->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->once())->method('getUsedProducts')->with($productMock)
            ->willReturn([$variationProductMock]);
        $variationProductMock->expects($this->once())->method('getId')->willReturn($variationProductId);
        $variationProductMock->expects($this->once())->method('getMediaGalleryImages')->willReturn($dataCollection);
        $dataCollection->expects($this->once())->method('getItems')->willReturn([$image]);
        $variationProductMock->expects($this->once())->method('getImage')->willReturn('image.jpg');
        $jsonMock->expects($this->once())->method('serialize')->with($expectedGalleryJson)
            ->willReturn(json_encode($expectedGalleryJson));

        $helper = new ObjectManager($this);
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
     * @return MockObject
     */
    private function createJsonMock()
    {
        return $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject
     */
    private function createProductMock()
    {
        return $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject
     */
    private function createGalleryMock()
    {
        return $this->getMockBuilder(\Magento\Catalog\Block\Product\View\Gallery::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return MockObject
     */
    private function createConfigurableTypeMock()
    {
        return $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
