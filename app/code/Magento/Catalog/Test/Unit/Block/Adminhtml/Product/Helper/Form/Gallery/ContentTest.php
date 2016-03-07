<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Framework\Filesystem;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readMock;

    /**
     * @var Content|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $content;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaConfigMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $galleryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->fileSystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->readMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->galleryMock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery',
            [],
            [],
            '',
            false
        );
        $this->mediaConfigMock = $this->getMock('Magento\Catalog\Model\Product\Media\Config', [], [], '', false);
        $this->jsonEncoderMock = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->content = $this->objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content',
            [
                'mediaConfig' => $this->mediaConfigMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'filesystem' => $this->fileSystemMock
            ]
        );
    }

    public function testGetImagesJson()
    {
        $url = [
            ['file_1.jpg', 'url_to_the_image/image_1.jpg'],
            ['file_2.jpg', 'url_to_the_image/image_2.jpg']
        ];
        $mediaPath = [
            ['file_1.jpg', 'catalog/product/image_1.jpg'],
            ['file_2.jpg', 'catalog/product/image_2.jpg']
        ];
        // @codingStandardsIgnoreStart
        $encodedString = '[{"value_id":"1","file":"image_1.jpg","media_type":"image","url":"http:\/\/magento2.dev\/pub\/media\/catalog\/product\/image_1.jpg","size":879394},{"value_id":"2","file":"image_2.jpg","media_type":"image","url":"http:\/\/magento2.dev\/pub\/media\/catalog\/product\/image`_2.jpg","size":399659}]';
        // @codingStandardsIgnoreEnd
        $images = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                ] ,
                [
                    'value_id' => '2',
                    'file' => 'file_2.jpg',
                    'media_type' => 'image',
                ]
            ]
        ];
        $firstStat = ['size' => 879394];
        $secondStat = ['size' => 399659];
        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->any())->method('getImages')->willReturn($images);
        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')->willReturn($this->readMock);

        $this->mediaConfigMock->expects($this->any())->method('getMediaUrl')->willReturnMap($url);
        $this->mediaConfigMock->expects($this->any())->method('getMediaPath')->willReturn($mediaPath);

        $this->readMock->expects($this->at(0))->method('stat')->willReturn($firstStat);
        $this->readMock->expects($this->at(1))->method('stat')->willReturn($secondStat);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($encodedString);

        $this->assertSame($encodedString, $this->content->getImagesJson());
    }

    public function testGetImagesJsonWithoutImages()
    {
        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn(null);

        $this->assertSame('[]', $this->content->getImagesJson());
    }
}
