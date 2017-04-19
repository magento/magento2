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
class GalleryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Gallery
     */
    private $plugin;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $json;

    /**
     * @var int
     */
    private $variationProductId = 1;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->json = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods(['getTypeId', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $variationProduct = $this->getMockBuilder(Product::class)
            ->setMethods(['setMediaGalleryEntries', 'getId', 'getMediaGalleryImages', 'getImage'])
            ->disableOriginalConstructor()
            ->getMock();
        $image = new \Magento\Framework\DataObject(
            ['media_type' => 'type', 'video_url' => 'url', 'file' => 'image.jpg']
        );
        $variationProduct->expects($this->any())->method('setMediaGalleryEntries')->willReturn([]);
        $variationProduct->expects($this->any())->method('getId')->willReturn($this->variationProductId);
        $variationProduct->expects($this->any())->method('getMediaGalleryImages')->willReturn([$image]);
        $variationProduct->expects($this->any())->method('getImage')->willReturn('image.jpg');

        $configurableType = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsedProducts'])
            ->getMock();
        $configurableType->expects($this->any())->method('getUsedProducts')->with($productMock)
            ->willReturn([$variationProduct]);

        $productMock->expects($this->any())->method('getTypeId')->willReturn('configurable');
        $productMock->expects($this->any())->method('getTypeInstance')->willReturn($configurableType);

        $this->plugin = $helper->getObject(
            Gallery::class,
            [
                'json' => $this->json
            ]
        );
        $this->plugin->setData('product', $productMock);
    }

    public function testAfterGetOptions()
    {
        $resultJson = '[]';
        $this->json->expects($this->once())->method('unserialize')->with('[]')->willReturn([]);
        $expected = [
            $this->variationProductId => [
                [
                    'mediaType' => 'type',
                    'videoUrl' => 'url',
                    'isBase' => true
                ]
            ]
        ];
        $this->json->expects($this->any())->method('serialize')->with($expected)
            ->willReturn(json_encode($expected));

        $blockMock = $this->getMockBuilder(\Magento\ProductVideo\Block\Product\View\Gallery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->plugin->afterGetOptionsMediaGalleryDataJson($blockMock, $resultJson);
        $this->assertEquals(json_encode($expected), $result);
    }
}
