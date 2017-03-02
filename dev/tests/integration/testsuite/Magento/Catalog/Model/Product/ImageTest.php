<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product;

/**
 * Class \Magento\Catalog\Model\Product\ImageTest
 * @magentoAppArea frontend
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function testSetBaseFilePlaceholder()
    {
        /** @var $model \Magento\Catalog\Model\Product\Image */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Image::class
        );
        /** @var \Magento\Catalog\Model\View\Asset\Placeholder $defualtPlaceholder */
        $defualtPlaceholder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\View\Asset\Placeholder::class,
                ['type' => 'image']
            );

        $model->setDestinationSubdir('image');
        $model->setBaseFile('');
        $this->assertEquals($defualtPlaceholder->getSourceFile(), $model->getBaseFile());
        return $model;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Image $model
     * @depends testSetBaseFilePlaceholder
     */
    public function testSaveFilePlaceholder($model)
    {
        $processor = $this->getMock(\Magento\Framework\Image::class, ['save'], [], '', false);
        $processor->expects($this->exactly(0))->method('save');
        $model->setImageProcessor($processor)->saveFile();
    }

    /**
     * @param \Magento\Catalog\Model\Product\Image $model
     * @depends testSetBaseFilePlaceholder
     */
    public function testGetUrlPlaceholder($model)
    {
        $this->assertStringMatchesFormat(
            'http://localhost/pub/static/%s/frontend/%s/Magento_Catalog/images/product/placeholder/image.jpg',
            $model->getUrl()
        );
    }

    public function testSetWatermark()
    {
        $inputFile = 'watermark.png';
        $expectedFile = '/somewhere/watermark.png';

        /** @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject $viewFilesystem */
        $viewFileSystem = $this->getMock(\Magento\Framework\View\FileSystem::class, [], [], '', false);
        $viewFileSystem->expects($this->once())
            ->method('getStaticFileName')
            ->with($inputFile)
            ->will($this->returnValue($expectedFile));

        /** @var $model \Magento\Catalog\Model\Product\Image */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product\Image::class, ['viewFileSystem' => $viewFileSystem]);
        $processor = $this->getMock(
            \Magento\Framework\Image::class,
            ['save', 'keepAspectRatio', 'keepFrame', 'keepTransparency', 'constrainOnly', 'backgroundColor', 'quality',
                'setWatermarkPosition', 'setWatermarkImageOpacity', 'setWatermarkWidth', 'setWatermarkHeight',
                'watermark'],
            [], '', false);
        $processor->expects($this->once())
            ->method('watermark')
            ->with($expectedFile);
        $model->setImageProcessor($processor);

        $model->setWatermark('watermark.png');
    }
}
