<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Catalog\Block\Product\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepository;

    /**
     * @var \Magento\Framework\View\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfig;

    /**
     * @var \Magento\Catalog\Model\Product\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $image;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    protected function setUp()
    {
        $this->mockContext();
        $this->mockImage();

        $this->assetRepository = $this->getMockBuilder('Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig = $this->getMockBuilder('Magento\Framework\View\ConfigInterface')
            ->getMockForAbstractClass();

        $this->helper = new \Magento\Catalog\Helper\Image(
            $this->context,
            $this->imageFactory,
            $this->assetRepository,
            $this->viewConfig
        );
    }

    protected function mockContext()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);
    }

    protected function mockImage()
    {
        $this->imageFactory = $this->getMockBuilder('Magento\Catalog\Model\Product\ImageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->image = $this->getMockBuilder('Magento\Catalog\Model\Product\Image')
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->image);
    }

    /**
     * @param array $data
     * @dataProvider initDataProvider
     */
    public function testInit($data)
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareAttributes($data, $imageId);
        $this->prepareImageProperties($data);
        $this->prepareWatermarkProperties($data);

        $this->assertInstanceOf(
            'Magento\Catalog\Helper\Image',
            $this->helper->init($productMock, $imageId, $attributes)
        );
    }

    /**
     * @return array
     */
    public function initDataProvider()
    {
        return [
            [
                'data' => [
                    'type' => 'image',
                    'width' => 100,
                    'height' => 100,
                    'frame' => 1,
                    'constrain' => 1,
                    'aspect_ratio' => 1,
                    'transparency' => 0,
                    'background' => [255, 255, 255],
                    'watermark' => 'watermark_file',
                    'watermark_opacity' => 100,
                    'watermark_position' => 1,
                    'watermark_size' => '100x100',
                    'watermark_size_array' => ['width' => 100, 'height' => 100],
                ],
            ],
        ];
    }

    /**
     * @param $data
     * @param $imageId
     */
    protected function prepareAttributes($data, $imageId)
    {
        $configViewMock = $this->getMockBuilder('Magento\Framework\Config\View')
            ->disableOriginalConstructor()
            ->getMock();
        $configViewMock->expects($this->once())
            ->method('getMediaAttributes')
            ->with('Magento_Catalog', 'images', $imageId)
            ->willReturn($data);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->willReturn($configViewMock);
    }

    /**
     * @param $data
     */
    protected function prepareImageProperties($data)
    {
        $this->image->expects($this->once())
            ->method('setDestinationSubdir')
            ->with($data['type'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('getDestinationSubdir')
            ->willReturn($data['type']);
        $this->image->expects($this->once())
            ->method('setWidth')
            ->with($data['width'])
            ->willReturnSelf();
        $this->image->expects($this->once())
            ->method('setHeight')
            ->with($data['height'])
            ->willReturnSelf();

        $this->image->expects($this->any())
            ->method('setKeepFrame')
            ->with($data['frame'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setConstrainOnly')
            ->with($data['constrain'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setKeepAspectRatio')
            ->with($data['aspect_ratio'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setKeepTransparency')
            ->with($data['transparency'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setBackgroundColor')
            ->with($data['background'])
            ->willReturnSelf();
    }

    /**
     * @param $data
     */
    protected function prepareWatermarkProperties($data)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                [
                    'design/watermark/' . $data['type'] . '_image',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['watermark']
                ],
                [
                    'design/watermark/' . $data['type'] . '_imageOpacity',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['watermark_opacity']
                ],
                [
                    'design/watermark/' . $data['type'] . '_position',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['watermark_position']
                ],
                [
                    'design/watermark/' . $data['type'] . '_size',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    null,
                    $data['watermark_size']
                ],
            ]);

        $this->image->expects($this->any())
            ->method('setWatermarkFile')
            ->with($data['watermark'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setWatermarkImageOpacity')
            ->with($data['watermark_opacity'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setWatermarkPosition')
            ->with($data['watermark_position'])
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('setWatermarkSize')
            ->with($data['watermark_size_array'])
            ->willReturnSelf();
    }

    public function testGetType()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $data = [
            'type' => 'image',
        ];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareAttributes($data, $imageId);

        $this->helper->init($productMock, $imageId, $attributes);
        $this->assertEquals($data['type'], $this->helper->getType());
    }

    public function testGetWidth()
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $data = [
            'width' => 100,
        ];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareAttributes($data, $imageId);

        $this->helper->init($productMock, $imageId, $attributes);
        $this->assertEquals($data['width'], $this->helper->getWidth());
    }

    /**
     * @param array $data
     * @dataProvider getHeightDataProvider
     */
    public function testGetHeight($data)
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareAttributes($data, $imageId);

        $height = isset($data['height']) ? $data['height'] : $data['width'];

        $this->helper->init($productMock, $imageId, $attributes);
        $this->assertEquals($height, $this->helper->getHeight());
    }

    /**
     * @return array
     */
    public function getHeightDataProvider()
    {
        return [
            'data' => [
                [
                    'height' => 100,
                ],
                [
                    'width' => 100,
                    'height' => 100,
                ],
                [
                    'width' => 100,
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @dataProvider getFrameDataProvider
     */
    public function testGetFrame($data)
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareAttributes($data, $imageId);

        $this->helper->init($productMock, $imageId, $attributes);
        $this->assertEquals($data['frame'], $this->helper->getFrame());
    }

    /**
     * @return array
     */
    public function getFrameDataProvider()
    {
        return [
            'data' => [
                [
                    'frame' => 0,
                ],
                [
                    'frame' => 1,
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @param string $expected
     * @dataProvider getLabelDataProvider
     */
    public function testGetLabel($data, $expected)
    {
        $imageId = 'test_image_id';
        $attributes = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('getData')
            ->with($data['type'] . '_' . 'label')
            ->willReturn($data['label']);
        $productMock->expects($this->any())
            ->method('getName')
            ->willReturn($expected);

        $this->prepareAttributes($data, $imageId);

        $this->helper->init($productMock, $imageId, $attributes);
        $this->assertEquals($expected, $this->helper->getLabel());
    }

    /**
     * @return array
     */
    public function getLabelDataProvider()
    {
        return [
            [
                'data' => [
                    'type' => 'image',
                    'label' => 'test_label',
                ],
                'test_label',
            ],
            [
                'data' => [
                    'type' => 'image',
                    'label' => null,
                ],
                'test_label',
            ],
        ];
    }

    /**
     * @param string $imageId
     * @param string $imageFile
     * @param string $baseFile
     * @param string $newFile
     * @param string $destination
     * @param boolean $setImageFile
     * @param boolean $isCached
     * @param boolean $isBaseFilePlaceholder
     * @param array $resizedImageInfo
     * @dataProvider getResizedImageInfoDataProvider
     */
    public function testGetResizedImageInfo(
        $imageId,
        $imageFile,
        $baseFile,
        $newFile,
        $destination,
        $setImageFile,
        $isCached,
        $isBaseFilePlaceholder,
        $resizedImageInfo
    ) {
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->any())
            ->method('getData')
            ->with($destination)
            ->willReturn($imageFile);

        $this->image->expects($this->any())
            ->method('setBaseFile')
            ->with($imageFile)
            ->willReturnSelf();
        $this->image->expects($this->once())
            ->method('getBaseFile')
            ->willReturn($baseFile);
        $this->image->expects($this->any())
            ->method('getDestinationSubdir')
            ->willReturn($destination);
        $this->image->expects($this->any())
            ->method('isCached')
            ->willReturn($isCached);
        $this->image->expects($this->any())
            ->method('resize')
            ->willReturnSelf();
        $this->image->expects($this->any())
            ->method('saveFile')
            ->willReturnSelf();
        $this->image->expects($this->once())
            ->method('getResizedImageInfo')
            ->willReturn($resizedImageInfo);
        $this->image->expects($this->any())
            ->method('isBaseFilePlaceholder')
            ->willReturn($isBaseFilePlaceholder);
        $this->image->expects($this->any())
            ->method('getNewFile')
            ->willReturn($newFile);

        $this->prepareAttributes([], $imageId);

        $this->helper->init($productMock, $imageId);
        if ($setImageFile) {
            $this->helper->setImageFile($imageFile);
        }

        $result = $this->helper->getResizedImageInfo();
        $this->assertEquals($resizedImageInfo, $result);
    }

    /**
     * @return array
     */
    public function getResizedImageInfoDataProvider()
    {
        return [
            [
                'image_id' => 'test_image_id',
                'image_file' => '/path/to/test_image_id.png',
                'base_file' => '/path/to/base_image.png',
                'new_file' => '/path/to/base_image.png',
                'destination' => 'small_image',
                'set_image_file' => true,
                'is_cached' => false,
                'is_base_file_placeholder' => false,
                'resized_image_info' => [
                    'x' => 100,
                    'y' => 100,
                ],
            ],
            [
                'image_id' => 'test_image_id',
                'image_file' => '/path/to/test_image_id.png',
                'base_file' => null,
                'new_file' => true,
                'destination' => 'small_image',
                'set_image_file' => false,
                'is_cached' => false,
                'is_base_file_placeholder' => false,
                'resized_image_info' => [
                    'x' => 100,
                    'y' => 100,
                ],
            ],
            [
                'image_id' => 'test_image_id',
                'image_file' => '/path/to/test_image_id.png',
                'base_file' => null,
                'new_file' => false,
                'destination' => 'small_image',
                'set_image_file' => true,
                'is_cached' => false,
                'is_base_file_placeholder' => false,
                'resized_image_info' => [
                    'x' => 100,
                    'y' => 100,
                ],
            ],
            [
                'image_id' => 'test_image_id',
                'image_file' => '/path/to/test_image_id.png',
                'base_file' => null,
                'new_file' => true,
                'destination' => 'small_image',
                'set_image_file' => true,
                'is_cached' => false,
                'is_base_file_placeholder' => true,
                'resized_image_info' => [
                    'x' => 100,
                    'y' => 100,
                ],
            ],
            [
                'image_id' => 'test_image_id',
                'image_file' => '/path/to/test_image_id.png',
                'base_file' => null,
                'new_file' => '/path/to/test_image_id.png',
                'destination' => 'small_image',
                'set_image_file' => true,
                'is_cached' => false,
                'is_base_file_placeholder' => false,
                'resized_image_info' => [
                    'x' => 100,
                    'y' => 100,
                ],
            ],
        ];
    }
}
