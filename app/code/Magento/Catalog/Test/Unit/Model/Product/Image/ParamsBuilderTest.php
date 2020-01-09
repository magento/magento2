<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\View;
use Magento\Framework\View\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ParamsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigInterface
     */
    private $viewConfig;

    /**
     * @var ParamsBuilder
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->viewConfig = $this->createMock(ConfigInterface::class);
        $this->model = $objectManager->getObject(
            ParamsBuilder::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'viewConfig' => $this->viewConfig,
            ]
        );
    }

    /**
     * Test watermark location.
     */
    public function testWatermarkLocation()
    {
        $imageArguments = [
            'type' => 'type',
            'height' => 'image_height',
            'width' => 'image_width',
            'angle' => 'angle',
            'background' => [1, 2, 3]
        ];
        $scopeId = 1;
        $quality = 100;
        $file = 'file';
        $width = 'width';
        $height = 'height';
        $size = "{$width}x{$height}";
        $opacity = 'opacity';
        $position = 'position';

        $viewMock = $this->createMock(View::class);
        $viewMock->expects($this->once())
            ->method('getVarValue')
            ->with('Magento_Catalog', 'product_image_white_borders')
            ->willReturn(true);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => Area::AREA_FRONTEND])
            ->willReturn($viewMock);

        $this->scopeConfig->expects($this->exactly(5))->method('getValue')->withConsecutive(
            [
                Image::XML_PATH_JPEG_QUALITY
            ],
            [
                "design/watermark/{$imageArguments['type']}_image",
                ScopeInterface::SCOPE_STORE,
                $scopeId,
            ],
            [
                "design/watermark/{$imageArguments['type']}_size",
                ScopeInterface::SCOPE_STORE],
            [
                "design/watermark/{$imageArguments['type']}_imageOpacity",
                ScopeInterface::SCOPE_STORE,
                $scopeId
            ],
            [
                "design/watermark/{$imageArguments['type']}_position",
                ScopeInterface::SCOPE_STORE,
                $scopeId
            ]
        )->willReturnOnConsecutiveCalls(
            $quality,
            $file,
            $size,
            $opacity,
            $position
        );

        $actual = $this->model->build($imageArguments, $scopeId);
        $expected = [
            'image_type' => $imageArguments['type'],
            'background' => $imageArguments['background'],
            'angle' => $imageArguments['angle'],
            'quality' => $quality,
            'keep_aspect_ratio' => true,
            'keep_frame' => true,
            'keep_transparency' => true,
            'constrain_only' => true,
            'watermark_file' => $file,
            'watermark_image_opacity' => $opacity,
            'watermark_position' => $position,
            'watermark_width' => $width,
            'watermark_height' => $height,
            'image_height' => $imageArguments['height'],
            'image_width' => $imageArguments['width'],
        ];

        $this->assertEquals(
            $expected,
            $actual
        );
    }
}
