<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GalleryTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * @var \Magento\Framework\View\Config
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

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

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->escaper = $this->objectManager->getObject(\Magento\Framework\Escaper::class);
        $this->context->expects($this->any())
            ->method('getEscaper')
            ->willReturn($this->escaper);
        $this->viewConfig = $this->getMockBuilder(\Magento\Framework\View\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configView = $this->getMockBuilder(\Magento\Framework\Config\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig->expects($this->any())
            ->method('getViewConfig')
            ->willReturn($this->configView);
        $this->context->expects($this->any())
            ->method('getViewConfig')
            ->willReturn($this->viewConfig);
    }

    public function testGetGalleryImagesJsonWithLabel()
    {
        $this->prepareGetGalleryImagesJsonMocks();
        $json = $this->model->getGalleryImagesJson();
        $decodedJson = json_decode($json, true);
        $this->assertEquals('product_page_image_small_url', $decodedJson[0]['thumb']);
        $this->assertEquals('product_page_image_medium_url', $decodedJson[0]['img']);
        $this->assertEquals('product_page_image_large_url', $decodedJson[0]['full']);
        $this->assertEquals('test_label', $decodedJson[0]['caption']);
        $this->assertEquals('2', $decodedJson[0]['position']);
        $this->assertEquals(false, $decodedJson[0]['isMain']);
        $this->assertEquals('test_media_type', $decodedJson[0]['type']);
        $this->assertEquals('test_video_url', $decodedJson[0]['videoUrl']);
    }

    public function testGetGalleryImagesJsonWithoutLabel()
    {
        $this->prepareGetGalleryImagesJsonMocks(false);
        $json = $this->model->getGalleryImagesJson();
        $decodedJson = json_decode($json, true);
        $this->assertEquals('test_product_name', $decodedJson[0]['caption']);
    }

    /**
     * @param bool $hasLabel
     */
    private function prepareGetGalleryImagesJsonMocks($hasLabel = true)
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
        $productTypeMock->expects($this->any())
            ->method('getStoreFilter')
            ->with($productMock)
            ->willReturn($storeMock);

        $productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($productTypeMock);
        $productMock->expects($this->any())
            ->method('getMediaGalleryImages')
            ->willReturn($this->getImagesCollectionWithPopulatedDataObject($hasLabel));
        $productMock->expects($this->any())
            ->method('getName')
            ->willReturn('test_product_name');

        $this->registry->expects($this->any())
            ->method('registry')
            ->with('product')
            ->willReturn($productMock);

        $this->imageHelper->expects($this->any())
            ->method('init')
            ->willReturnMap([
                [$productMock, 'product_page_image_small', [], $this->imageHelper],
                [$productMock, 'product_page_image_medium_no_frame', [], $this->imageHelper],
                [$productMock, 'product_page_image_large_no_frame', [], $this->imageHelper],
            ])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('setImageFile')
            ->with('test_file')
            ->willReturnSelf();
        $this->imageHelper->expects($this->at(2))
            ->method('getUrl')
            ->willReturn('product_page_image_small_url');
        $this->imageHelper->expects($this->at(5))
            ->method('getUrl')
            ->willReturn('product_page_image_medium_url');
        $this->imageHelper->expects($this->at(8))
            ->method('getUrl')
            ->willReturn('product_page_image_large_url');
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

    /**
     * @return \Magento\Framework\Data\Collection
     */
    private function getImagesCollectionWithPopulatedDataObject($hasLabel)
    {
        $collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [
            new \Magento\Framework\DataObject([
                'file' => 'test_file',
                'label' => ($hasLabel ? 'test_label' : ''),
                'position' => '2',
                'media_type' => 'external-test_media_type',
                "video_url" => 'test_video_url'
            ]),
        ];

        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        return $collectionMock;
    }

    public function testGalleryOptions()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/nav', 'thumbs'],
            ['Magento_Catalog', 'gallery/loop', 'false'],
            ['Magento_Catalog', 'gallery/keyboard', 'true'],
            ['Magento_Catalog', 'gallery/arrows', 'true'],
            ['Magento_Catalog', 'gallery/caption', 'false'],
            ['Magento_Catalog', 'gallery/allowfullscreen', 'true'],
            ['Magento_Catalog', 'gallery/navdir', 'horizontal'],
            ['Magento_Catalog', 'gallery/navarrows', 'true'],
            ['Magento_Catalog', 'gallery/navtype', 'slides'],
            ['Magento_Catalog', 'gallery/thumbmargin', '5'],
            ['Magento_Catalog', 'gallery/transition/effect', 'slide'],
            ['Magento_Catalog', 'gallery/transition/duration', '500'],
        ];

        $mediaAttributesMap = [
            [
                'Magento_Catalog',
                \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE,
                'product_page_image_medium',
                [
                    'height' => 100,
                    'width' => 200
                ]
            ],
            [
                'Magento_Catalog',
                \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE,
                'product_page_image_small',
                [
                    'height' => 300,
                    'width' => 400
                ]
            ],
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->will($this->returnValueMap($configMap));
        $this->configView->expects($this->any())
            ->method('getMediaAttributes')
            ->will($this->returnValueMap($mediaAttributesMap));

        $json = $this->model->getGalleryOptionsJson();
        $decodedJson = json_decode($json, true);

        $this->assertEquals('thumbs', $decodedJson['nav']);
        $this->assertEquals(false, $decodedJson['loop']);
        $this->assertEquals(true, $decodedJson['keyboard']);
        $this->assertEquals(true, $decodedJson['arrows']);
        $this->assertEquals(false, $decodedJson['showCaption']);
        $this->assertEquals(true, $decodedJson['allowfullscreen']);
        $this->assertEquals('horizontal', $decodedJson['navdir']);
        $this->assertEquals(true, $decodedJson['navarrows']);
        $this->assertEquals('slides', $decodedJson['navtype']);
        $this->assertEquals(5, $decodedJson['thumbmargin']);
        $this->assertEquals('slide', $decodedJson['transition']);
        $this->assertEquals(500, $decodedJson['transitionduration']);
        $this->assertEquals(100, $decodedJson['height']);
        $this->assertEquals(200, $decodedJson['width']);
        $this->assertEquals(300, $decodedJson['thumbheight']);
        $this->assertEquals(400, $decodedJson['thumbwidth']);
    }

    public function testGalleryFSOptions()
    {
        $configMap = [
            ['Magento_Catalog', 'gallery/fullscreen/nav', 'false'],
            ['Magento_Catalog', 'gallery/fullscreen/loop', 'true'],
            ['Magento_Catalog', 'gallery/fullscreen/keyboard', 'false'],
            ['Magento_Catalog', 'gallery/fullscreen/arrows', 'false'],
            ['Magento_Catalog', 'gallery/fullscreen/caption', 'true'],
            ['Magento_Catalog', 'gallery/fullscreen/navdir', 'vertical'],
            ['Magento_Catalog', 'gallery/fullscreen/navarrows', 'false'],
            ['Magento_Catalog', 'gallery/fullscreen/navtype', 'thumbs'],
            ['Magento_Catalog', 'gallery/fullscreen/thumbmargin', '10'],
            ['Magento_Catalog', 'gallery/fullscreen/transition/effect', 'dissolve'],
            ['Magento_Catalog', 'gallery/fullscreen/transition/duration', '300']
        ];

        $this->configView->expects($this->any())
            ->method('getVarValue')
            ->will($this->returnValueMap($configMap));

        $json = $this->model->getGalleryFSOptionsJson();
        $decodedJson = json_decode($json, true);

        //Note, this tests the special case for nav variable set to false. It
        //Should not be converted to boolean.
        $this->assertEquals('false', $decodedJson['nav']);

        $this->assertEquals(true, $decodedJson['loop']);
        $this->assertEquals(false, $decodedJson['arrows']);
        $this->assertEquals(false, $decodedJson['keyboard']);
        $this->assertEquals(true, $decodedJson['showCaption']);
        $this->assertEquals('vertical', $decodedJson['navdir']);
        $this->assertEquals(false, $decodedJson['navarrows']);
        $this->assertEquals(10, $decodedJson['thumbmargin']);
        $this->assertEquals('thumbs', $decodedJson['navtype']);
        $this->assertEquals('dissolve', $decodedJson['transition']);
        $this->assertEquals(300, $decodedJson['transitionduration']);
    }
}
