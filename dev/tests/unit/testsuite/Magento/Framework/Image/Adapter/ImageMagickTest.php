<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image\Adapter;

class ImageMagickTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider watermarkDataProvider
     */
    public function testWatermark($imagePath, $expectedMessage)
    {
        $filesystem =
            $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock();
        $this->setExpectedException('LogicException', $expectedMessage);
        $object = new \Magento\Framework\Image\Adapter\ImageMagick($filesystem);
        $object->watermark($imagePath);
    }

    /**
     * @return array
     */
    public function watermarkDataProvider()
    {
        return [
            ['', \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            [__DIR__ . '/not_exists', \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WATERMARK_IMAGE_ABSENT],
            [
                __DIR__ . '/_files/invalid_image.jpg',
                \Magento\Framework\Image\Adapter\ImageMagick::ERROR_WRONG_IMAGE
            ]
        ];
    }
}
