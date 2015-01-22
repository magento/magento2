<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\App\Area;
use Magento\TestFramework\Helper\ObjectManager;

class ResizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Image\Resize
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Framework\View\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $config;

    /**
     * @var \Magento\Core\Model\Resource\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCollection;

    /**
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    protected function setUp()
    {
        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewConfig = $this->getMockBuilder('Magento\Framework\View\ConfigInterface')
            ->getMock();

        $this->config = $this->getMockBuilder('Magento\Framework\Config\View')
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeCollection = $this->getMockBuilder('Magento\Core\Model\Resource\Theme\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageHelper = $this->getMockBuilder('Magento\Catalog\Helper\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\Catalog\Model\Product\Image\Resize',
            [
                'viewConfig' => $this->viewConfig,
                'themeCollection' => $this->themeCollection,
                'imageHelper' => $this->imageHelper,
            ]
        );
    }

    public function testResize()
    {
        $imageFile = 'image.jpg';
        $imageItem = $this->objectManager->getObject(
            'Magento\Framework\Object',
            [
                'data' => ['file' => $imageFile]
            ]
        );
        $this->product->expects($this->once())
            ->method('getMediaGalleryImages')
            ->willReturn([$imageItem]);

        $this->config->expects($this->once())
            ->method('getVars')
            ->with('Magento_Catalog')
            ->willReturn($this->getTestData());

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with([
                'area' => Area::AREA_FRONTEND,
                'themeModel' => 'Magento\theme',
            ])
            ->willReturn($this->config);

        $this->themeCollection->expects($this->once())
            ->method('loadRegisteredThemes')
            ->willReturn(['Magento\theme']);

        $this->imageHelper->expects($this->exactly(3))
            ->method('init')
            ->will($this->returnValueMap([
                [$this->product, 'image', $imageFile, $this->imageHelper],
                [$this->product, 'small_image', $imageFile, $this->imageHelper],
                [$this->product, 'thumbnail', $imageFile, $this->imageHelper],
            ]));
        $this->imageHelper->expects($this->exactly(3))
            ->method('resize')
            ->will($this->returnValueMap([
                [300, 300, $this->imageHelper],
                [200, 200, $this->imageHelper],
                [100, 100, $this->imageHelper],
            ]));
        $this->imageHelper->expects($this->exactly(3))
            ->method('save')
            ->will($this->returnSelf());

        $this->model->resize($this->product);
    }

    /**
     * @return array
     */
    protected function getTestData()
    {
        return [
            // Wrong format
            'product_image_size' => 100,

            // Ok
            'product_image:type' => 'image',
            'product_image:width' => 300,
            'product_image:height' => 300,

            // Ok
            'product_small_image:type' => 'small_image',
            'product_small_image:width' => 200,
            'product_small_image:height' => 200,

            // Ok
            'product_thumbnail:type' => 'thumbnail',
            'product_thumbnail:width' => 100,
            'product_thumbnail:height' => 100,

            // Missing required parameter 'type'
            'product_image_wrong_one:width' => 100,
            'product_image_wrong_one:height' => 100,

            // Missing required parameter 'height'
            'product_image_wrong_two:type' => 'thumbnail',
            'product_image_wrong_two:width' => 100,

            // Missing required parameter 'width'
            'product_image_wrong_three:type' => 'thumbnail',
            'product_image_wrong_three:height' => 100,
        ];
    }
}
