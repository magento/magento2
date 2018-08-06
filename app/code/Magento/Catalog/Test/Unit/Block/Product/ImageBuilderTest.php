<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;

class ImageBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImageBuilder
     */
    private $model;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helperFactory;

    /**
     * @var ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageFactory;

    protected function setUp()
    {
        $this->helperFactory = $this->createPartialMock(\Magento\Catalog\Helper\ImageFactory::class, ['create']);

        $this->imageFactory = $this->createPartialMock(ImageFactory::class, ['create']);

        $this->model = new ImageBuilder($this->helperFactory, $this->imageFactory);
    }

    public function testSetProduct()
    {
        $productMock = $this->createMock(Product::class);

        $this->assertInstanceOf(
            ImageBuilder::class,
            $this->model->setProduct($productMock)
        );
    }

    public function testSetImageId()
    {
        $imageId = 'test_image_id';

        $this->assertInstanceOf(
            ImageBuilder::class,
            $this->model->setImageId($imageId)
        );
    }

    public function testSetAttributes()
    {
        $attributes = [
            'name' => 'value',
        ];
        $this->assertInstanceOf(
            ImageBuilder::class,
            $this->model->setAttributes($attributes)
        );
    }

    /**
     * @param array $data
     * @dataProvider createDataProvider
     */
    public function testCreate($data, $expected)
    {
        $imageId = 'test_image_id';

        $productMock = $this->createMock(Product::class);

        $helperMock = $this->createMock(Image::class);
        $helperMock->expects($this->once())
            ->method('init')
            ->with($productMock, $imageId)
            ->willReturnSelf();

        $helperMock->expects($this->once())
            ->method('getFrame')
            ->willReturn($data['frame']);
        $helperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($data['url']);
        $helperMock->expects($this->exactly(2))
            ->method('getWidth')
            ->willReturn($data['width']);
        $helperMock->expects($this->exactly(2))
            ->method('getHeight')
            ->willReturn($data['height']);
        $helperMock->expects($this->once())
            ->method('getLabel')
            ->willReturn($data['label']);
        $helperMock->expects($this->once())
            ->method('getResizedImageInfo')
            ->willReturn($data['imagesize']);

        $this->helperFactory->expects($this->once())
            ->method('create')
            ->willReturn($helperMock);

        $imageMock = $this->createMock(\Magento\Catalog\Block\Product\Image::class);

        $this->imageFactory->expects($this->once())
            ->method('create')
            ->with($expected)
            ->willReturn($imageMock);

        $this->model->setProduct($productMock);
        $this->model->setImageId($imageId);
        $this->model->setAttributes($data['custom_attributes']);
        $this->assertInstanceOf(\Magento\Catalog\Block\Product\Image::class, $this->model->create());
    }

    /**
     * Check if custom attributes will be overridden when builder used few times
     * @param array $data
     * @dataProvider createMultipleCallsDataProvider
     */
    public function testCreateMultipleCalls($data)
    {
        list ($firstCall, $secondCall) = array_values($data);

        $imageId = 'test_image_id';

        $productMock = $this->createMock(Product::class);

        $helperMock = $this->createMock(Image::class);
        $helperMock->expects($this->exactly(2))
            ->method('init')
            ->with($productMock, $imageId)
            ->willReturnSelf();

        $helperMock->expects($this->exactly(2))
            ->method('getFrame')
            ->willReturnOnConsecutiveCalls($firstCall['data']['frame'], $secondCall['data']['frame']);
        $helperMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls($firstCall['data']['url'], $secondCall['data']['url']);
        $helperMock->expects($this->exactly(4))
            ->method('getWidth')
            ->willReturnOnConsecutiveCalls(
                $firstCall['data']['width'],
                $firstCall['data']['width'],
                $secondCall['data']['width'],
                $secondCall['data']['width']
            );
        $helperMock->expects($this->exactly(4))
            ->method('getHeight')
            ->willReturnOnConsecutiveCalls(
                $firstCall['data']['height'],
                $firstCall['data']['height'],
                $secondCall['data']['height'],
                $secondCall['data']['height']
            );
        $helperMock->expects($this->exactly(2))
            ->method('getLabel')
            ->willReturnOnConsecutiveCalls($firstCall['data']['label'], $secondCall['data']['label']);
        $helperMock->expects($this->exactly(2))
            ->method('getResizedImageInfo')
            ->willReturnOnConsecutiveCalls($firstCall['data']['imagesize'], $secondCall['data']['imagesize']);
        $this->helperFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($helperMock);

        $imageMock = $this->createMock(\Magento\Catalog\Block\Product\Image::class);

        $this->imageFactory->expects($this->at(0))
            ->method('create')
            ->with($firstCall['expected'])
            ->willReturn($imageMock);

        $this->imageFactory->expects($this->at(1))
            ->method('create')
            ->with($secondCall['expected'])
            ->willReturn($imageMock);

        $this->model->setProduct($productMock);
        $this->model->setImageId($imageId);
        $this->model->setAttributes($firstCall['data']['custom_attributes']);

        $this->assertInstanceOf(\Magento\Catalog\Block\Product\Image::class, $this->model->create());

        $this->model->setProduct($productMock);
        $this->model->setImageId($imageId);
        $this->model->setAttributes($secondCall['data']['custom_attributes']);
        $this->assertInstanceOf(\Magento\Catalog\Block\Product\Image::class, $this->model->create());
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
    public function createMultipleCallsDataProvider(): array
    {
        return [
            [
                [
                    'without_attributes' => $this->getTestDataWithoutAttributes(),
                    'with_attributes' => $this->getTestDataWithAttributes(),
                ],
            ],
            [
                [
                    'with_attributes' => $this->getTestDataWithAttributes(),
                    'without_attributes' => $this->getTestDataWithoutAttributes(),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getTestDataWithoutAttributes(): array
    {
        return [
            'data' => [
                'frame' => 0,
                'url' => 'test_url_1',
                'width' => 100,
                'height' => 100,
                'label' => 'test_label',
                'custom_attributes' => [],
                'imagesize' => [100, 100],
            ],
            'expected' => [
                'data' => [
                    'template' => 'Magento_Catalog::product/image_with_borders.phtml',
                    'image_url' => 'test_url_1',
                    'width' => 100,
                    'height' => 100,
                    'label' => 'test_label',
                    'ratio' => 1,
                    'custom_attributes' => '',
                    'resized_image_width' => 100,
                    'resized_image_height' => 100,
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
                'frame' => 1,
                'url' => 'test_url_2',
                'width' => 100,
                'height' => 50,
                'label' => 'test_label_2',
                'custom_attributes' => [
                    'name_1' => 'value_1',
                    'name_2' => 'value_2',
                ],
                'imagesize' => [120, 70],
            ],
            'expected' => [
                'data' => [
                    'template' => 'Magento_Catalog::product/image.phtml',
                    'image_url' => 'test_url_2',
                    'width' => 100,
                    'height' => 50,
                    'label' => 'test_label_2',
                    'ratio' => 0.5,
                    'custom_attributes' => 'name_1="value_1" name_2="value_2"',
                    'resized_image_width' => 120,
                    'resized_image_height' => 70,
                    'product_id' => null
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider createDataProvider
     */
    public function testCreateWithSimpleProduct($data, $expected)
    {
        $imageId = 'test_image_id';

        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $simpleProductMock = $this->createMock(\Magento\Catalog\Model\Product::class);

        $helperMock = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $helperMock->expects($this->once())
            ->method('init')
            ->with($simpleProductMock, $imageId)
            ->willReturnSelf();
        $helperMock->expects($this->once())
            ->method('getFrame')
            ->willReturn($data['frame']);
        $helperMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($data['url']);
        $helperMock->expects($this->exactly(2))
            ->method('getWidth')
            ->willReturn($data['width']);
        $helperMock->expects($this->exactly(2))
            ->method('getHeight')
            ->willReturn($data['height']);
        $helperMock->expects($this->once())
            ->method('getLabel')
            ->willReturn($data['label']);
        $helperMock->expects($this->once())
            ->method('getResizedImageInfo')
            ->willReturn($data['imagesize']);

        $this->helperFactory->expects($this->once())
            ->method('create')
            ->willReturn($helperMock);

        $imageMock = $this->createMock(\Magento\Catalog\Block\Product\Image::class);

        $this->imageFactory->expects($this->once())
            ->method('create')
            ->with($expected)
            ->willReturn($imageMock);

        $this->model->setProduct($productMock);
        $this->model->setImageId($imageId);
        $this->model->setAttributes($data['custom_attributes']);

        $this->assertInstanceOf(\Magento\Catalog\Block\Product\Image::class, $this->model->create());
    }
}
