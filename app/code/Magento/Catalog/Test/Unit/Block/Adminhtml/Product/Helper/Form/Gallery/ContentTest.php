<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form\Gallery;

use Magento\Framework\Filesystem;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content;
use Magento\Framework\Phrase;

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
     * @var \Magento\Catalog\Helper\Image|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->fileSystemMock = $this->getMock(
            'Magento\Framework\Filesystem',
            ['stat', 'getDirectoryRead'],
            [],
            '',
            false
        );
        $this->readMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->galleryMock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery',
            [],
            [],
            '',
            false
        );
        $this->mediaConfigMock = $this->getMock(
            'Magento\Catalog\Model\Product\Media\Config',
            ['getMediaUrl', 'getMediaPath'],
            [],
            '',
            false
        );
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

        $sizeMap = [
            ['catalog/product/image_1.jpg', ['size' => 399659]],
            ['catalog/product/image_2.jpg', ['size' => 879394]]
        ];

        $imagesResult = [
            [
                'value_id' => '2',
                'file' => 'file_2.jpg',
                'media_type' => 'image',
                'position' => '0',
                'url' => 'url_to_the_image/image_2.jpg',
                'size' => 879394
            ],
            [
                'value_id' => '1',
                'file' => 'file_1.jpg',
                'media_type' => 'image',
                'position' => '1',
                'url' => 'url_to_the_image/image_1.jpg',
                'size' => 399659
            ]
        ];

        $images = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                    'position' => '1'
                ] ,
                [
                    'value_id' => '2',
                    'file' => 'file_2.jpg',
                    'media_type' => 'image',
                    'position' => '0'
                ]
            ]
        ];

        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn($images);
        $this->fileSystemMock->expects($this->once())->method('getDirectoryRead')->willReturn($this->readMock);

        $this->mediaConfigMock->expects($this->any())->method('getMediaUrl')->willReturnMap($url);
        $this->mediaConfigMock->expects($this->any())->method('getMediaPath')->willReturnMap($mediaPath);
        $this->readMock->expects($this->any())->method('stat')->willReturnMap($sizeMap);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturnCallback('json_encode');

        $this->assertSame(json_encode($imagesResult), $this->content->getImagesJson());
    }

    public function testGetImagesJsonWithoutImages()
    {
        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn(null);

        $this->assertSame('[]', $this->content->getImagesJson());
    }

    public function testGetImagesJsonWithException()
    {
        $this->imageHelper = $this->getMockBuilder('Magento\Catalog\Helper\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultPlaceholderUrl'])
            ->getMock();
        
        $this->objectManager->setBackwardCompatibleProperty(
            $this->content,
            'imageHelper',
            $this->imageHelper          
        );

        $placeholderUrl = 'url_to_the_placeholder/placeholder.jpg';

        $imagesResult = [
            [
                'value_id' => '2',
                'file' => 'file_2.jpg',
                'media_type' => 'image',
                'position' => '0',
                'url' => 'url_to_the_placeholder/placeholder.jpg',
                'size' => 0
            ],
            [
                'value_id' => '1',
                'file' => 'file_1.jpg',
                'media_type' => 'image',
                'position' => '1',
                'url' => 'url_to_the_placeholder/placeholder.jpg',
                'size' => 0
            ]
        ];

        $images = [
            'images' => [
                [
                    'value_id' => '1',
                    'file' => 'file_1.jpg',
                    'media_type' => 'image',
                    'position' => '1'
                ],
                [
                    'value_id' => '2',
                    'file' => 'file_2.jpg',
                    'media_type' => 'image',
                    'position' => '0'
                ]
            ]
        ];

        $this->content->setElement($this->galleryMock);
        $this->galleryMock->expects($this->once())->method('getImages')->willReturn($images);
        $this->fileSystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($this->readMock);
        $this->mediaConfigMock->expects($this->any())->method('getMediaUrl');
        $this->mediaConfigMock->expects($this->any())->method('getMediaPath');
        $this->readMock->expects($this->any())->method('stat')->willReturnOnConsecutiveCalls(
            $this->throwException(
                new \Magento\Framework\Exception\FileSystemException(new Phrase('test'))
            ),
            $this->throwException(
                new \Magento\Framework\Exception\FileSystemException(new Phrase('test'))
            )
        );
        $this->imageHelper->expects($this->any())->method('getDefaultPlaceholderUrl')->willReturn($placeholderUrl);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturnCallback('json_encode');

        $this->assertSame(json_encode($imagesResult), $this->content->getImagesJson());
        }
}
