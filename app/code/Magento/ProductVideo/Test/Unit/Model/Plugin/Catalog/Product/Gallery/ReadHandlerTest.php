<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductVideo\Test\Unit\Model\Plugin\Catalog\Product\Gallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery\ReadHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for plugin for catalog product gallery read handler.
 */
class ReadHandlerTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var ReadHandler|MockObject
     */
    protected $subject;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var Attribute|MockObject
     */
    protected $attribute;

    /**
     * @var Gallery|MockObject
     */
    protected $resourceModel;

    /**
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler|MockObject
     */
    protected $mediaGalleryReadHandler;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);

        $this->attribute = $this->createMock(Attribute::class);
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('media_gallery');

        $this->resourceModel = $this->createMock(Gallery::class);

        $this->mediaGalleryReadHandler = $this->createMock(\Magento\Catalog\Model\Product\Gallery\ReadHandler::class);

        $objectManager = new ObjectManager($this);

        $this->subject = $objectManager->getObject(
            ReadHandler::class,
            [
                'resourceModel' => $this->resourceModel
            ]
        );
    }

    public function testAfterExecute()
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

        $this->product->expects($this->once())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn($mediaData);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn(0);

        $this->resourceModel->expects($this->once())
            ->method('loadDataFromTableByValueId')
            ->willReturn($resourceEntryResult);

        $this->mediaGalleryReadHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->subject->afterExecute(
            $this->mediaGalleryReadHandler,
            $this->product
        );
    }

    public function testAfterExecuteNoVideo()
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

        $this->product->expects($this->once())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn($mediaData);

        $this->resourceModel->expects($this->never())->method('loadDataFromTableByValueId');

        $this->mediaGalleryReadHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->subject->afterExecute(
            $this->mediaGalleryReadHandler,
            $this->product
        );
    }
}
