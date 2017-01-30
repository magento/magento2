<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Model\Plugin;

class ExternalVideoEntryProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\ResourceModel\ProductFactory */
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceEntryMediaGalleryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute */
    protected $attributeMock;

    protected $mediaBackendModel;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryProcessor
     */
    protected $modelObject;

    public function setUp()
    {
        $this->productFactoryMock =
            $this->getMock('\Magento\Catalog\Model\ResourceModel\ProductFactory', ['create'], [], '', false);

        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);

        $this->fileStorageDbMock =
            $this->getMock('\Magento\MediaStorage\Helper\File\Storage\Database', [], [], '', false);

        $this->jsonHelperMock = $this->getMock('\Magento\Framework\Json\Helper\Data', [], [], '', false);

        $this->mediaConfigMock = $this->getMock('\Magento\Catalog\Model\Product\Media\Config', [], [], '', false);

        $this->filesystemMock = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $write = $this->getMock('\Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->with('media')->willReturn($write);

        $this->resourceEntryMediaGalleryMock =
            $this->getMock('\Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Media', [], [], '', false);

        $this->attributeMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->attributeMock->expects($this->any())->method('getAttributeCode')->willReturn('media_gallery');

        $this->mediaBackendModel = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Backend\Media',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->modelObject = $objectManager->getObject(
            '\Magento\ProductVideo\Model\Plugin\ExternalVideoEntryProcessor',
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

    public function testAfterAfterLoad()
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

        $this->mediaBackendModel->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->modelObject->afterAfterLoad($this->mediaBackendModel, $this->productMock);
    }

    public function testAfterAfterSave()
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
                    'additional_store_data' => [
                        0 =>
                            [
                                'store_id' => '0',
                                'video_provider' => null,
                                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                                'video_title' => 'New Title',
                                'video_description' => 'New Description',
                                'video_metadata' => 'New metadata',
                            ],
                    ]
                ],
            ],
        ];

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn(0);
        $this->mediaBackendModel->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->modelObject->afterAfterSave($this->mediaBackendModel, $this->productMock);
    }

    public function testAfterAfterSaveEmpty()
    {
        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn([]);
        $this->mediaBackendModel->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);
        $this->modelObject->afterAfterSave($this->mediaBackendModel, $this->productMock);
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

        $this->productMock->expects($this->once())->method('getData')->with('media_gallery')->willReturn($mediaData);
        $this->resourceEntryMediaGalleryMock->expects($this->never())->method('loadDataFromTableByValueId');
        $this->mediaBackendModel->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->modelObject->afterAfterLoad($this->mediaBackendModel, $this->productMock);
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
                ],
                'w596fi79hv1p6wj21u' => [
                    'position' => '4',
                    'media_type' => 'external-video',
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
                    'video_provider' => '',
                    'file' => '/h/d/hd_image.jpg',
                    'value_id' => '',
                    'label' => '',
                    'disabled' => '0',
                    'removed' => '',
                    'video_url' => '',
                    'video_title' => '',
                    'video_description' => '',
                    'video_metadata' => '',
                    'role' => '',
                    'save_data_from' => '7',
                    'new_file' => '/i/n/index_4.jpg',
                ],
            ],
        ];

        $this->productMock->expects($this->any())->method('getData')->with('media_gallery')->willReturn($mediaData);
        $this->productMock->expects($this->any())->method('getStoreId')->willReturn(0);

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
                'value_id' => '7',
                'store_id' => 1,
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
        $this->mediaBackendModel->expects($this->any())->method('getAttribute')->willReturn($this->attributeMock);

        $this->modelObject->afterBeforeSave($this->mediaBackendModel, $this->productMock);
    }
}
