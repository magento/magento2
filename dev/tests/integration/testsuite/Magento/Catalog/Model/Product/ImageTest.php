<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
            'Magento\Catalog\Model\Product\Image'
        );
        $model->setDestinationSubdir('image')->setBaseFile('');
        $this->assertEmpty($model->getBaseFile());
        return $model;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Image $model
     * @depends testSetBaseFilePlaceholder
     */
    public function testSaveFilePlaceholder($model)
    {
        $processor = $this->getMock('Magento\Framework\Image', array('save'), array(), '', false);
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
            'http://localhost/pub/static/frontend/%s/Magento_Catalog/images/product/placeholder/image.jpg',
            $model->getUrl()
        );
    }

    public function testSetWatermark()
    {
        $inputFile = 'watermark.png';
        $expectedFile = '/somewhere/watermark.png';

        /** @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject $viewFilesystem */
        $viewFileSystem = $this->getMock('Magento\Framework\View\FileSystem', array(), array(), '', false);
        $viewFileSystem->expects($this->once())
            ->method('getStaticFileName')
            ->with($inputFile)
            ->will($this->returnValue($expectedFile));

        /** @var $model \Magento\Catalog\Model\Product\Image */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product\Image', ['viewFileSystem' => $viewFileSystem]);
        $processor = $this->getMock(
            'Magento\Framework\Image',
            ['save', 'keepAspectRatio', 'keepFrame', 'keepTransparency', 'constrainOnly', 'backgroundColor', 'quality',
                'setWatermarkPosition', 'setWatermarkImageOpacity', 'setWatermarkWidth', 'setWatermarkHeight',
                'watermark'],
            array(), '', false);
        $processor->expects($this->once())
            ->method('watermark')
            ->with($expectedFile);
        $model->setImageProcessor($processor);

        $model->setWatermark('watermark.png');
    }
}
