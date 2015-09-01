<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Model\Product\Attribute\Media;

class ExternalVideoMediaGalleryEntryProcessorTest extends \PHPUnit_Framework_TestCase
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
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoMediaGalleryEntryProcessor
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
        $write = $this->getMock('\Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystemMock->expects($this->once())->method('getDirectoryWrite')->with('media')->willReturn($write);

        $this->resourceEntryMediaGalleryMock =
            $this->getMock('\Magento\Catalog\Model\Resource\Product\Attribute\Backend\Media', [], [], '', false);

        $this->attributeMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('media_gallery');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            '\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoMediaGalleryEntryProcessor',
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
        $mediaData = [
            'images' => [
                [
                    'value_id' => '4',
                    'file' => '/i/n/index111111.jpg',
                    'media_type' => 'external-video',
                    'entity_id' => '1',
                    'label' => '',
                    'position' => '3',
                    'disabled' => '0',
                    'label_default' => null,
                    'position_default' => '3',
                    'disabled_default' => '0',
                ],
                [
                    'value_id' => '5',
                    'file' => '/s/a/sample_3.jpg',
                    'media_type' => 'external-video',
                    'entity_id' => '1',
                    'label' => '',
                    'position' => '4',
                    'disabled' => '0',
                    'label_default' => null,
                    'position_default' => '4',
                    'disabled_default' => '0',
                ],
                [
                    'value_id' => '7',
                    'file' => '/h/d/hd_image.jpg',
                    'media_type' => 'image',
                    'entity_id' => '1',
                    'label' => '',
                    'position' => '4',
                    'disabled' => '0',
                    'label_default' => null,
                    'position_default' => '4',
                    'disabled_default' => '0',
                ]
            ],
            'values' => []
        ];

        $resourceEntryResult = [
            [
                'value_id' => '4',
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title_default' => 'Some first title',
                'video_description_default' => 'Description first',
                'video_metadata_default' => 'meta one',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title' => 'Some first title',
                'video_description' => 'Description first',
                'video_metadata' => 'meta one',
            ],
            [
                'value_id' => '5',
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title_default' => 'Some second title',
                'video_description_default' => 'Description second',
                'video_metadata_default' => 'meta two',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title' => 'Some second title',
                'video_description' => 'Description second',
                'video_metadata' => '',
            ]
        ];

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn(0);

        $this->resourceEntryMediaGalleryMock->expects($this->once())->method('loadDataFromTableByValueId')->willReturn(
            $resourceEntryResult
        );

        $this->modelObject->afterLoad($this->productMock, $this->attributeMock);
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
                    'removed' => '',
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
                    'value_id' => '5',
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
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn(0);

        $this->modelObject->afterSave($this->productMock, $this->attributeMock);
    }

    public function testAfterSaveEmpty()
    {
        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn([]);
        $this->modelObject->afterSave($this->productMock, $this->attributeMock);
    }

    public function testAfterLoadNoVideo()
    {
        $mediaData = [
            'images' => [
                [
                    'value_id' => '7',
                    'file' => '/h/d/hd_image.jpg',
                    'media_type' => 'image',
                    'entity_id' => '1',
                    'label' => '',
                    'position' => '4',
                    'disabled' => '0',
                    'label_default' => null,
                    'position_default' => '4',
                    'disabled_default' => '0',
                ]
            ],
            'values' => []
        ];

        $resourceEntryResult = [];

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);

        $this->resourceEntryMediaGalleryMock->expects($this->once())->method('loadDataFromTableByValueId')->willReturn(
            $resourceEntryResult
        );

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
                    'removed' => '',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
                    'video_title' => 'Some second title',
                    'video_description' => 'Description second',
                    'video_metadata' => 'meta two',
                    'role' => '',
                    'save_data_from' => '4',
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
                    'value_id' => '5',
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
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn(0);

        $resourceEntryResult = [
            [
                'value_id' => '4',
                'store_id' => 1,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title_default' => 'Some first title',
                'video_description_default' => 'Description first',
                'video_metadata_default' => 'meta one',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=abcdefghij',
                'video_title' => 'Some first title',
                'video_description' => 'Description first',
                'video_metadata' => 'meta one',
            ],
            [
                'value_id' => '5',
                'store_id' => 0,
                'video_provider_default' => 'youtube',
                'video_url_default' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title_default' => 'Some second title',
                'video_description_default' => 'Description second',
                'video_metadata_default' => 'meta two',
                'video_provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                'video_title' => 'Some second title',
                'video_description' => 'Description second',
                'video_metadata' => '',
            ]
        ];

        $this->resourceEntryMediaGalleryMock->expects($this->once())->method('loadDataFromTableByValueId')->willReturn(
            $resourceEntryResult
        );

        $this->modelObject->beforeSave($this->productMock, $this->attributeMock);
    }
}
