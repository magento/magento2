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
use Magento\ProductVideo\Model\Plugin\Catalog\Product\Gallery\CreateHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for plugin for catalog product gallery Create handler.
 */
class CreateHandlerTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var CreateHandler
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
     * @var \Magento\Catalog\Model\Product\Gallery\CreateHandler|MockObject
     */
    protected $mediaGalleryCreateHandler;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);

        $this->attribute = $this->createMock(Attribute::class);
        $this->attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn('media_gallery');

        $this->resourceModel = $this->createMock(Gallery::class);

        $this->mediaGalleryCreateHandler = $this->createMock(
            \Magento\Catalog\Model\Product\Gallery\CreateHandler::class
        );

        $objectManager = new ObjectManager($this);

        $this->subject = $objectManager->getObject(
            CreateHandler::class,
            [
                'resourceModel' => $this->resourceModel
            ]
        );
    }

    /**
     * @dataProvider provideImageForAfterExecute
     * @param array $image
     * @param array $expectedSave
     * @param int $rowSaved
     */
    public function testAfterExecute($image, $expectedSave, $rowSaved): void
    {
        $this->product->expects($this->once())
            ->method('getData')
            ->with('media_gallery')
            ->willReturn(['images' => $image]);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn(0);

        $this->mediaGalleryCreateHandler->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->attribute);

        $this->resourceModel->expects($this->exactly($rowSaved))
            ->method('saveDataRow')
            ->with('catalog_product_entity_media_gallery_value_video', $expectedSave)
            ->willReturn(1);

        $this->subject->afterExecute($this->mediaGalleryCreateHandler, $this->product);
    }

    /**
     * DataProvider for testAfterExecute
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function provideImageForAfterExecute(): array
    {
        return [
            'new_video' => [
                [
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
                ],
                [
                    'value_id' => '4',
                    'store_id' => 0,
                    'provider' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=ab123456',
                    'title' => 'Some second title',
                    'description' => 'Description second',
                    'metadata' => 'meta two',
                ],
                2
            ],
            'image' => [
                [
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
                ],
                [],
                0
            ],
            'new_video_with_additional_data' => [
                [
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
                            0 => [
                                'store_id' => 0,
                                'video_provider' => 'youtube',
                                'video_url' => 'https://www.youtube.com/watch?v=ab123456',
                                'video_title' => 'Some second title',
                                'video_description' => 'Description second',
                                'video_metadata' => 'meta two',
                            ],
                        ]
                    ],
                ],
                [
                    'value_id' => '5',
                    'store_id' => 0,
                    'provider' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=ab123456',
                    'title' => 'Some second title',
                    'description' => 'Description second',
                    'metadata' => 'meta two',
                ],
                3
            ],
            'not_new_video' => [
                [
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
                        'video_url_default' => 'https://www.youtube.com/watch?v=ab123456',
                        'video_title' => 'Some second title',
                        'video_title_default' => 'Some second title',
                        'video_description' => 'Description second',
                        'video_metadata' => 'meta two',
                        'role' => '',
                    ],
                ],
                [
                    'value_id' => '4',
                    'store_id' => 0,
                    'provider' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=ab123456',
                    'title' => 'Some second title',
                    'description' => 'Description second',
                    'metadata' => 'meta two',
                ],
                1
            ],
        ];
    }

    /**
     * Tests empty media gallery
     */
    public function testAfterExecuteEmpty(): void
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
    public function testBeforeExecute(): void
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
