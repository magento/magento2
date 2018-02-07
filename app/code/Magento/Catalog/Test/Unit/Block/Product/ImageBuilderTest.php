<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

class ImageBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperFactory;

    /**
     * @var \Magento\Catalog\Block\Product\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageFactory;

    protected function setUp()
    {
        $this->helperFactory = $this->getMockBuilder('Magento\Catalog\Helper\ImageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->imageFactory = $this->getMockBuilder('Magento\Catalog\Block\Product\ImageFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = new \Magento\Catalog\Block\Product\ImageBuilder(
            $this->helperFactory,
            $this->imageFactory
        );
    }

    public function testSetProduct()
    {
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\ImageBuilder',
            $this->model->setProduct($productMock)
        );
    }

    public function testSetImageId()
    {
        $imageId = 'test_image_id';

        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\ImageBuilder',
            $this->model->setImageId($imageId)
        );
    }

    public function testSetAttributes()
    {
        $attributes = [
            'name' => 'value',
        ];
        $this->assertInstanceOf(
            'Magento\Catalog\Block\Product\ImageBuilder',
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

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $helperMock = $this->getMockBuilder('Magento\Catalog\Helper\Image')
            ->disableOriginalConstructor()
            ->getMock();
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

        $imageMock = $this->getMockBuilder('Magento\Catalog\Block\Product\Image')
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageFactory->expects($this->once())
            ->method('create')
            ->with($expected)
            ->willReturn($imageMock);

        $this->model->setProduct($productMock);
        $this->model->setImageId($imageId);
        $this->model->setAttributes($data['custom_attributes']);
        $this->assertInstanceOf('Magento\Catalog\Block\Product\Image', $this->model->create());
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [
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
                        'ratio' =>  1,
                        'custom_attributes' => '',
                        'resized_image_width' => 100,
                        'resized_image_height' => 100,
                    ],
                ],
            ],
            [
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
                        'ratio' =>  0.5,
                        'custom_attributes' => 'name_1="value_1" name_2="value_2"',
                        'resized_image_width' => 120,
                        'resized_image_height' => 70,
                    ],
                ],
            ],
        ];
    }
}
