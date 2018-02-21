<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Model\Plugin\Catalog\Product\Gallery;

/**
 * Unit test for plugin for catalog product gallery Create handler.
 */
class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery\CreateHandler
     */
    protected $subject;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Gallery|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModel;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\CreateHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mediaGalleryCreateHandler;

    protected function setUp()
    {
        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            [],
            [],
            '',
            false
        );

        $this->attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            [],
            [],
            '',
            false
        );
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('media_gallery');

        $this->resourceModel = $this->getMock(
            'Magento\Catalog\Model\ResourceModel\Product\Gallery',
            [],
            [],
            '',
            false
        );

        $this->mediaGalleryCreateHandler = $this->getMock(
            'Magento\Catalog\Model\Product\Gallery\CreateHandler',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->subject = $objectManager->getObject(
            'Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery\CreateHandler',
            [
                'resourceModel' => $this->resourceModel
            ]
        );
    }

    public function testAfterExecute()
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

        $this->product->expects($this->once())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn($mediaData);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn(0);

        $this->mediaGalleryCreateHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->subject->afterExecute(
            $this->mediaGalleryCreateHandler,
            $this->product
        );
    }

    public function testAfterExecuteEmpty()
    {
        $this->product->expects($this->once())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn([]);

        $this->mediaGalleryCreateHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->subject->afterExecute(
            $this->mediaGalleryCreateHandler,
            $this->product
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBeforeExecute()
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

        $this->product->expects($this->any())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn($mediaData);
        $this->product->expects($this->any())
            ->method('getStoreId')
            ->willReturn(0);

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

        $this->resourceModel->expects($this->once())
            ->method('loadDataFromTableByValueId')
            ->willReturn($resourceEntryResult);

        $this->mediaGalleryCreateHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->subject->beforeExecute(
            $this->mediaGalleryCreateHandler,
            $this->product
        );
    }
}
