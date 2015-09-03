<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\Media;

class ImageMediaGalleryEntryProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Resource\ProductFactory */
    protected $productFactoryMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product */
    protected $productMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\MediaStorage\Helper\File\Storage\Database */
    protected $fileStorageDbMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Json\Helper\Data */
    protected $jsonHelperMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Media\Config */
    protected $mediaConfigMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem */
    protected $filesystemMock;

    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface */
    protected $mediaDirectoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media
     */
    protected $resourceEntryMediaGalleryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute */
    protected $attributeMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageMediaGalleryEntryProcessor
     */
    protected $modelObject;

    public function setUp()
    {
        $this->productFactoryMock =
            $this->getMock('\Magento\Catalog\Model\Resource\ProductFactory', ['create'], [], '', false);

        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $this->fileStorageDbMock =
            $this->getMock('\Magento\MediaStorage\Helper\File\Storage\Database', [], [], '', false);

        $this->jsonHelperMock = $this->getMock('\Magento\Framework\Json\Helper\Data', [], [], '', false);

        $this->mediaConfigMock = $this->getMock('\Magento\Catalog\Model\Product\Media\Config', [], [], '', false);

        $this->filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->mediaDirectoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->with('media')->willReturn(
            $this->mediaDirectoryMock
        );

        $this->resourceEntryMediaGalleryMock =
            $this->getMock('\Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media', [], [], '', false);

        $this->attributeMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('media_gallery');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            '\Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageMediaGalleryEntryProcessor',
            [
                'productFactory' => $this->productFactoryMock,
                'fileStorageDb' => $this->fileStorageDbMock,
                'jsonHelper' => $this->jsonHelperMock,
                'mediaConfig' => $this->mediaConfigMock,
                'filesystem' => $this->filesystemMock,
                'resourceEntryMediaGallery' => $this->resourceEntryMediaGalleryMock
            ]
        );
    }

    public function testAfterLoad()
    {
        $mediaEntries = [
            [
                'value_id' => '5',
                'file' => '/s/a/sample_3.jpg',
                'media_type' => 'external-video',
                'entity_id' => '1',
                'label' => null,
                'position' => '4',
                'disabled' => '0',
                'label_default' => 'default label',
                'position_default' => '4',
                'disabled_default' => '0',
            ],
            [
                'value_id' => '8',
                'file' => '/s/a/sample1_l.jpg',
                'media_type' => 'external-video',
                'entity_id' => '1',
                'label' => null,
                'position' => '5',
                'disabled' => '0',
                'label_default' => null,
                'position_default' => '5',
                'disabled_default' => '0',
            ],
            [
                'value_id' => '6',
                'file' => '/s/a/sample-1_1.jpg',
                'media_type' => 'image',
                'entity_id' => '1',
                'label' => null,
                'position' => '5',
                'disabled' => '0',
                'label_default' => null,
                'position_default' => '5',
                'disabled_default' => '0',
            ]
        ];

        $this->attributeMock->expects($this->once())->method('getId')->willReturn(5);

        $this->resourceEntryMediaGalleryMock->expects($this->once())->method('loadProductGalleryByAttributeId')->with(
            $this->productMock,
            5
        )->willReturn($mediaEntries);

        $this->modelObject->afterLoad($this->productMock, $this->attributeMock);
    }

    public function testBeforeSave()
    {
        $mediaData = [
            'images' => [
                '72mljfhmasfilp9cuq' => [
                    'position' => '3',
                    'media_type' => 'external-video',
                    'file' => '/i/n/index111111.jpg',
                    'value_id' => '4',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '1',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                ],
                'w596fi79hv1p6wj21u' => [
                    'position' => '4',
                    'media_type' => 'image',
                    'video_provider' => '',
                    'file' => '/h/d/hd_image.jpg',
                    'value_id' => '7',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_url' => '',
                    'video_title' => '',
                    'video_description' => '',
                    'video_metadata' => '',
                    'role' => '',
                ],
                'sdegw45tw45tseg34r' => [
                    'position' => '4',
                    'media_type' => 'image',
                    'video_provider' => '',
                    'file' => '/h/d/hd_image2.jpg',
                    'value_id' => '',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_url' => '',
                    'video_title' => '',
                    'video_description' => '',
                    'video_metadata' => '',
                    'role' => '',
                ],
                'tcodwd7e0dirifr64j' => [
                    'position' => '4',
                    'media_type' => 'external-video',
                    'file' => '/s/a/sample_3.jpg',
                    'value_id' => '',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                ],
            ],
        ];

        $this->fileStorageDbMock->expects($this->exactly(2))->method('checkDbUsage')->willReturnOnConsecutiveCalls(
            true,
            false
        );

        $imageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $imageAttr->expects($this->once())->method('getAttributeCode')->willReturn('image');

        $smallImageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $smallImageAttr->expects($this->once())->method('getAttributeCode')->willReturn('small_image');

        $thumbAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $thumbAttr->expects($this->once())->method('getAttributeCode')->willReturn('thumbnail');

        $this->productMock->expects($this->exactly(4))->method('getData')->withConsecutive(
            ['media_gallery'],
            ['image'],
            ['small_image'],
            ['thumbnail']
        )->willReturnOnConsecutiveCalls(
            $mediaData,
            '/s/a/sample_3.jpg',
            '/h/d/hd_image.jpg',
            '/i/n/index111111.jpg'
        );

        $this->productMock->expects($this->once())->method('getMediaAttributes')->willReturn(
            [$imageAttr, $smallImageAttr, $thumbAttr]
        );

        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }

    public function testBeforeSaveEmpty()
    {
        $mediaData = [];
        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);
        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }

    public function testBeforeSaveDuplicate()
    {
        $jsonData = '{"images":{"72mljfhmasfilp9cuq":{"position":"3","media_type":"external-video","file"'
            . ':"\/i\/n\/index111111.jpg","value_id":"4","label":"","disabled":"0","removed":"1","video_provider":'
            . '"youtube","video_url":"https:\/\/www.youtube.com\/watch?v=ab123456","video_title":"Some second title"'
            . ',"video_description":"Description second","video_metadata":"meta two","role":""},"w596fi79hv1p6wj21u"'
            . ':{"position":"4","media_type":"image","video_provider":"","file":"\/h\/d\/hd_image.jpg","value_id":'
            . '"7","label":"","disabled":"0","removed":"","video_url":"","video_title":"","video_description":"",'
            . '"video_metadata":"","role":""}}}';

        $mediaData = [
            'images' => $jsonData
        ];

        $arrayData = [
            '72mljfhmasfilp9cuq' => [
                'position' => '3',
                'media_type' => 'external-video',
                'file' => '/i/n/index111111.jpg',
                'value_id' => '4',
                'label' => '',
                'disabled' => '0',
                'removed' => '1',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title' => 'Some second title',
                'video_description' => 'Description second',
                'video_metadata' => 'meta two',
                'role' => '',
            ],
            'w596fi79hv1p6wj21u' => [
                'position' => '4',
                'media_type' => 'image',
                'video_provider' => '',
                'file' => '/h/d/hd_image.jpg',
                'value_id' => '7',
                'label' => '',
                'disabled' => '0',
                'removed' => '',
                'video_url' => '',
                'video_title' => '',
                'video_description' => '',
                'video_metadata' => '',
                'role' => '',
            ],
        ];

        $this->jsonHelperMock->expects($this->once())->method('jsonDecode')->with($jsonData)->willReturn($arrayData);

        $this->productMock->expects($this->once())->method('__call')->with('getIsDuplicate')->willReturn(true);

        $this->mediaConfigMock->expects($this->any())->method('getMediaPath')->willReturn('some_path');

        $this->fileStorageDbMock->expects($this->exactly(2))->method('checkDbUsage')->willReturnOnConsecutiveCalls(
            true,
            false
        );
        $this->fileStorageDbMock->expects($this->once())->method('copyFile')->will($this->returnSelf());

        $this->mediaDirectoryMock->expects($this->exactly(2))->method('isFile')->willReturn(true);
        $this->mediaDirectoryMock->expects($this->exactly(1))->method('copyFile')->will(
            $this->returnSelf()
        );

        $imageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $imageAttr->expects($this->once())->method('getAttributeCode')->willReturn('image');

        $smallImageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $smallImageAttr->expects($this->once())->method('getAttributeCode')->willReturn('small_image');

        $thumbAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $thumbAttr->expects($this->once())->method('getAttributeCode')->willReturn('thumbnail');

        $this->productMock->expects($this->exactly(4))->method('getData')->withConsecutive(
            ['media_gallery'],
            ['image'],
            ['small_image'],
            ['thumbnail']
        )->willReturnOnConsecutiveCalls(
            $mediaData,
            '/s/a/sample_3.jpg',
            '/s/a/sample-1_1.jpg',
            '/s/a/sample-1_1.jpg'
        );

        $this->productMock->expects($this->once())->method('getMediaAttributes')->willReturn(
            [$imageAttr, $smallImageAttr, $thumbAttr]
        );

        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }

    public function testBeforeSaveEmptyImageKey()
    {
        $value = 'string, not json';

        $mediaData = [
            'images' => $value
        ];

        $this->jsonHelperMock->expects($this->once())->method('jsonDecode')->with($value)->willReturn(false);

        $this->productMock->expects($this->once())->method('__call')->with('getIsDuplicate')->willReturn(true);

        $imageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $imageAttr->expects($this->once())->method('getAttributeCode')->willReturn('image');

        $smallImageAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $smallImageAttr->expects($this->once())->method('getAttributeCode')->willReturn('small_image');

        $thumbAttr = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $thumbAttr->expects($this->once())->method('getAttributeCode')->willReturn('thumbnail');

        $this->productMock->expects($this->exactly(4))->method('getData')->withConsecutive(
            ['media_gallery'],
            ['image'],
            ['small_image'],
            ['thumbnail']
        )->willReturnOnConsecutiveCalls(
            $mediaData,
            '/s/a/sample_3.jpg',
            '/s/a/sample-1_1.jpg',
            '/s/a/sample-1_1.jpg'
        );

        $this->productMock->expects($this->once())->method('getMediaAttributes')->willReturn(
            [$imageAttr, $smallImageAttr, $thumbAttr]
        );

        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }

    public function testBeforeSaveDuplicateException()
    {
        $mediaData = [
            'images' => [
                '72mljfhmasfilp9cuq' => [
                    'position' => '3',
                    'media_type' => 'external-video',
                    'file' => '/i/n/index111111.jpg',
                    'value_id' => '',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '1',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                ],
                'w596fi79hv1p6wj21u' => [
                    'position' => '4',
                    'media_type' => 'image',
                    'video_provider' => '',
                    'file' => '/h/d/hd_image.jpg',
                    'value_id' => '7',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_url' => '',
                    'video_title' => '',
                    'video_description' => '',
                    'video_metadata' => '',
                    'role' => '',
                ],
            ]
        ];

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);

        $this->productMock->expects($this->once())->method('__call')->with('getIsDuplicate')->willReturn(true);

        $this->mediaConfigMock->expects($this->any())->method('getMediaPath')->willReturn('some_path');
        $this->mediaDirectoryMock->expects($this->once())->method('isFile')->willReturn(false);

        $this->setExpectedException('\Exception');

        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }

    public function testAfterSave()
    {
        $mediaData = [
            'images' => [
                '72mljfhmasfilp9cuq' => [
                    'position' => '3',
                    'media_type' => 'external-video',
                    'file' => '/i/n/index111111.jpg',
                    'value_id' => '4',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '1',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                ],
                'w596fi79hv1p6wj21u' => [
                    'position' => '4',
                    'media_type' => 'image',
                    'video_provider' => '',
                    'file' => '/h/d/hd_image.jpg',
                    'value_id' => '7',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_url' => '',
                    'video_title' => '',
                    'video_description' => '',
                    'video_metadata' => '',
                    'role' => '',
                ],
                'tcodwd7e0dirifr64j' => [
                    'position' => '4',
                    'media_type' => 'external-video',
                    'file' => '/s/a/sample_3.jpg',
                    'value_id' => '',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                ],
            ],
        ];

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);

        $productResourceMock = $this->getMock('\Magento\Catalog\Model\Resource\Product', [], [], '', false);

        $assignedImage = [
            'tcodwd7e0dirifr64j' => [
                'filepath' => '/s/a/sample_3.jpg',
            ],
        ];

        $productResourceMock->expects($this->once())->method('getAssignedImages')->willReturn($assignedImage);
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($productResourceMock);

        $this->modelObject->afterSave($this->productMock, $this->attributeMock);
    }

    public function testAfterSaveEmpty()
    {
        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn([]);
        $this->modelObject->afterSave($this->productMock, $this->attributeMock);
    }

    public function testAfterSaveDuplicate()
    {
        $this->productMock->expects($this->once())->method('__call')->with('getIsDuplicate')->willReturn(true);

        $backendModel = $this->getMock('\Magento\Catalog\Model\Product\Attribute\Backend\Media', [], [], '', false);
        $backendModel->expects($this->once())->method('duplicate')->with($this->productMock)->willReturn(
            $this->productMock
        );

        $this->attributeMock->expects($this->once())->method('getBackendModel')->willReturn($backendModel);

        $this->modelObject->afterSave($this->productMock, $this->attributeMock);
    }
}
