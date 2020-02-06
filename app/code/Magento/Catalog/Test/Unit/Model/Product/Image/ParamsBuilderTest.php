<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Image;

use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\View;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test product image params builder
 */
class ParamsBuilderTest extends TestCase
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
    /**
     * @var array
     */
    private $scopeConfigData = [];

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->viewConfig = $this->createMock(ConfigInterface::class);
        $this->model = $objectManager->getObject(
            ParamsBuilder::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'viewConfig' => $this->viewConfig,
            ]
        );
        $this->scopeConfigData = [];
        $this->scopeConfig->method('getValue')
            ->willReturnCallback(
                function ($path, $scopeType, $scopeCode) {
                    return $this->scopeConfigData[$path][$scopeType][$scopeCode] ?? null;
                }
            );
    }

    /**
     * Test build() with different parameters and config values
     *
     * @param int $scopeId
     * @param array $config
     * @param array $imageArguments
     * @param array $expected
     * @dataProvider buildDataProvider
     */
    public function testBuild(int $scopeId, array $config, array $imageArguments, array $expected)
    {
        $this->scopeConfigData[Image::XML_PATH_JPEG_QUALITY][ScopeConfigInterface::SCOPE_TYPE_DEFAULT][null] = 80;
        foreach ($config as $path => $value) {
            $this->scopeConfigData[$path][ScopeInterface::SCOPE_STORE][$scopeId] = $value;
        }
        $imageArguments += [
            'type' => 'image',
            'height' => '600',
            'width' => '400',
            'angle' => '45',
            'background' => [110, 64, 224]
        ];

        $viewMock = $this->createMock(View::class);
        $viewMock->expects($this->once())
            ->method('getVarValue')
            ->with('Magento_Catalog', 'product_image_white_borders')
            ->willReturn(true);

        $this->viewConfig->expects($this->once())
            ->method('getViewConfig')
            ->with(['area' => Area::AREA_FRONTEND])
            ->willReturn($viewMock);

        $actual = $this->model->build($imageArguments, $scopeId);
        $expected += [
            'image_type' => $imageArguments['type'],
            'background' => $imageArguments['background'],
            'angle' => $imageArguments['angle'],
            'quality' => 80,
            'keep_aspect_ratio' => true,
            'keep_frame' => true,
            'keep_transparency' => true,
            'constrain_only' => true,
            'image_height' => $imageArguments['height'],
            'image_width' => $imageArguments['width'],
        ];

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * Provides test scenarios for
     *
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            'watermark config' => [
                1,
                [
                    'design/watermark/small_image_image' => 'stores/1/magento-logo.png',
                    'design/watermark/small_image_size' => '60x40',
                    'design/watermark/small_image_imageOpacity' => '50',
                    'design/watermark/small_image_position' => 'bottom-right',
                ],
                [
                    'type' => 'small_image'
                ],
                [
                    'watermark_file' => 'stores/1/magento-logo.png',
                    'watermark_image_opacity' => '50',
                    'watermark_position' => 'bottom-right',
                    'watermark_width' => '60',
                    'watermark_height' => '40',
                ]
            ],
            'watermark config empty' => [
                1,
                [
                    'design/watermark/small_image_image' => 'stores/1/magento-logo.png',
                ],
                [
                    'type' => 'small_image'
                ],
                [
                    'watermark_file' => 'stores/1/magento-logo.png',
                    'watermark_image_opacity' => null,
                    'watermark_position' => null,
                    'watermark_width' => null,
                    'watermark_height' => null,
                ]
            ]
        ];
    }
}
