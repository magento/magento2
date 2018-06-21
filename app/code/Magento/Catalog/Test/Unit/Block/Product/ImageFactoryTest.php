<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\Image as ViewAssetImage;
use Magento\Catalog\Model\View\Asset\ImageFactory as ViewAssetImageFactory;
use Magento\Framework\Config\View;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\ObjectManager\ObjectManager;

class ImageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var  ParamsBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $paramsBuilder;

    /** @var  View|\PHPUnit_Framework_MockObject_MockObject */
    private $viewConfig;

    /** @var  ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /**
     * @var ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var ViewAssetImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewAssetImageFactory;

    protected function setUp()
    {
        $this->viewConfig = $this->createMock(View::class);
        $configInterface = $this->createMock(ConfigInterface::class);
        $configInterface->method('getViewConfig')->willReturn($this->viewConfig);
        $this->viewAssetImageFactory = $this->createMock(ViewAssetImageFactory::class);
        $this->paramsBuilder = $this->createMock(ParamsBuilder::class);
        $this->objectManager = $this->createMock(ObjectManager::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            ImageFactory::class,
            [
                'objectManager' => $this->objectManager,
                'presentationConfig' => $configInterface,
                'viewAssetImageFactory' => $this->viewAssetImageFactory,
                'imageParamsBuilder' => $this->paramsBuilder
            ]
        );
    }

    /**
     * @param array $data
     * @dataProvider createDataProvider
     */
    public function testCreate($data, $expected)
    {
        $product = $this->createMock(Product::class);
        $product->method('getName')->willReturn($data['product']['name']);
        $product->method('getData')->willReturnOnConsecutiveCalls(
            $data['product']['image_type'],
            $data['product']['image_type_label']
        );
        $imageBlock = $this->createMock(Image::class);
        $this->viewConfig->method('getMediaAttributes')->willReturn($data['viewImageConfig']);
        $this->viewConfig->method('getVarValue')->willReturn($data['frame']);
        $this->viewAssetImageFactory->method('create')->willReturn(
            $viewAssetImage = $this->createMock(ViewAssetImage::class)
        );
        $this->paramsBuilder->method('build')->willReturn($data['imageParamsBuilder']);
        $viewAssetImage->method('getUrl')->willReturn($data['url']);

        $this->objectManager->expects(self::once())
            ->method('create')
            ->with(Image::class, $expected)
            ->willReturn($imageBlock);
        $actual = $this->model->create($product, 'image_id', $data['custom_attributes']);
        self::assertInstanceOf(Image::class, $actual);
    }

    /**
     * @return array
     */
    public function createDataProvider(): array
    {
        return [
            $this->getTestDataWithoutAttributes(),
            $this->getTestDataWithAttributes(),
        ];
    }

    /**
     * @return array
     */
    private function getTestDataWithoutAttributes(): array
    {
        return [
            'data' => [
                'viewImageConfig' => [
                    'width' => 100,
                    'height' => 100,
                    'constrain_only' => false,
                    'aspect_ratio' => false,
                    'frame' => false,
                    'transparency' => false,
                    'background' => '255,255,255',
                    'type' => 'image_type' //thumbnail,small_image,image,swatch_image,swatch_thumb
                ],
                'imageParamsBuilder' => [
                    'image_width' => 100,
                    'image_height' => 100,
                    'constrain_only' => false,
                    'keep_aspect_ratio' => false,
                    'keep_frame' => false,
                    'keep_transparency' => false,
                    'background' => '255,255,255',
                    'image_type' => 'image_type', //thumbnail,small_image,image,swatch_image,swatch_thumb
                    'quality' => 80, // <===
                    'angle' => null // <===
                ],
                'product' => [
                    'image_type_label' => 'test_image_label',
                    'name' => 'test_product_name',
                    'image_type' => 'test_image_path'
                ],
                'url' => 'test_url_1',
                'frame' => 'test_frame',
                'custom_attributes' => [],
            ],
            'expected' => [
                'data' => [
                    'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                    'image_url' => 'test_url_1',
                    'width' => 100,
                    'height' => 100,
                    'label' => 'test_image_label',
                    'ratio' => 1,
                    'custom_attributes' => '',
                    'product_id' => null
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getTestDataWithAttributes(): array
    {
        return [
            'data' => [
                'viewImageConfig' => [
                    'width' => 100,
                    'height' => 50, // <===
                    'constrain_only' => false,
                    'aspect_ratio' => false,
                    'frame' => true, // <===
                    'transparency' => false,
                    'background' => '255,255,255',
                    'type' => 'image_type' //thumbnail,small_image,image,swatch_image,swatch_thumb
                ],
                'imageParamsBuilder' => [
                    'image_width' => 100,
                    'image_height' => 50,
                    'constrain_only' => false,
                    'keep_aspect_ratio' => false,
                    'keep_frame' => true,
                    'keep_transparency' => false,
                    'background' => '255,255,255',
                    'image_type' => 'image_type', //thumbnail,small_image,image,swatch_image,swatch_thumb
                    'quality' => 80,
                    'angle' => null
                ],
                'product' => [
                    'image_type_label' => null, // <==
                    'name' => 'test_product_name',
                    'image_type' => 'test_image_path'
                ],
                'url' => 'test_url_2',
                'frame' => 'test_frame',
                'custom_attributes' => [
                    'name_1' => 'value_1',
                    'name_2' => 'value_2',
                ],
            ],
            'expected' => [
                'data' => [
                    'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                    'image_url' => 'test_url_2',
                    'width' => 100,
                    'height' => 50,
                    'label' => 'test_product_name',
                    'ratio' => 0.5, // <==
                    'custom_attributes' => 'name_1="value_1" name_2="value_2"',
                    'product_id' => null
                ],
            ],
        ];
    }
}
