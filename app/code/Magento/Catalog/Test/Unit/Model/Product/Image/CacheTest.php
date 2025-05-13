<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\Cache;
use Magento\Framework\App\Area;
use Magento\Framework\Config\View;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Cache
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $viewConfig;

    /**
     * @var View|MockObject
     */
    protected $config;

    /**
     * @var Collection|MockObject
     */
    protected $themeCollection;

    /**
     * @var Image|MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Data\Collection|MockObject
     */
    protected $mediaGalleryCollection;

    protected function setUp(): void
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageHelper = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaGalleryCollection = $this->getMockBuilder(\Magento\Framework\Data\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Cache::class,
            [
                'viewConfig' => $this->viewConfig,
                'themeCollection' => $this->themeCollection,
                'imageHelper' => $this->imageHelper,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGenerate()
    {
        $imageFile = 'image.jpg';
        $imageItem = $this->objectManager->getObject(
            DataObject::class,
            [
                'data' => ['file' => $imageFile]
            ]
        );
        $this->mediaGalleryCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$imageItem]));

        $this->product->expects($this->any())
            ->method('getMediaGalleryImages')
            ->willReturn($this->mediaGalleryCollection);

        $data = $this->getTestData();
        $this->config->expects($this->once())
            ->method('getMediaEntities')
            ->with('Magento_Catalog')
            ->willReturn($data);

        $themeMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();
        $themeMock->expects($this->exactly(3))
            ->method('getCode')
            ->willReturn('Magento\theme');

        $this->themeCollection->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn([$themeMock]);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with([
                'area' => Area::AREA_FRONTEND,
                'themeModel' => $themeMock,
            ])
            ->willReturn($this->config);

        $this->imageHelper->expects($this->exactly(3))
            ->method('init')
            ->willReturnMap([
                [
                    $this->product,
                    'product_image',
                    $this->getImageData('product_image'),
                    $this->imageHelper
                ],
                [
                    $this->product,
                    'product_small_image',
                    $this->getImageData('product_small_image'),
                    $this->imageHelper
                ],
                [
                    $this->product,
                    'product_thumbnail',
                    $this->getImageData('product_thumbnail'),
                    $this->imageHelper
                ],
            ]);
        $this->imageHelper->expects($this->exactly(3))
            ->method('setImageFile')
            ->with($imageFile)
            ->willReturnSelf();

        $this->imageHelper->expects($this->any())
            ->method('keepAspectRatio')
            ->with($data['product_image']['aspect_ratio'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('keepFrame')
            ->with($data['product_image']['frame'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('keepTransparency')
            ->with($data['product_image']['transparency'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('constrainOnly')
            ->with($data['product_image']['constrain'])
            ->willReturnSelf();
        $this->imageHelper->expects($this->any())
            ->method('backgroundColor')
            ->with($data['product_image']['background'])
            ->willReturnSelf();

        $this->imageHelper->expects($this->exactly(3))
            ->method('save')->willReturnSelf();

        $this->model->generate($this->product);
    }

    /**
     * @return array
     */
    protected static function getTestData()
    {
        return [
            'product_image' => [
                'type' => 'image',
                'width' => 300,
                'height' => 300,
                'aspect_ratio' => true,
                'frame' => true,
                'transparency' => true,
                'constrain' => true,
                'background' => [255, 255, 255],
            ],
            'product_small_image' => [
                'type' => 'small_image',
                'height' => 200,
            ],
            'product_thumbnail' => [
                'type' => 'thumbnail',
                'width' => 100,
            ],
        ];
    }

    /**
     * @param string $imageId
     * @return array
     */
    protected function getImageData($imageId)
    {
        $imageData = $this->getTestData();
        return [
            'id' => $imageId,
            'type' => $imageData[$imageId]['type'],
            'width' => isset($imageData[$imageId]['width']) ? $imageData[$imageId]['width'] : null,
            'height' => isset($imageData[$imageId]['height']) ? $imageData[$imageId]['height'] : null,
        ];
    }
}
