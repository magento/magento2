<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ThumbnailTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface $context;

    /**
     * @var UiComponentFactory|MockObject
     */
    private UiComponentFactory $uiComponentFactory;

    /**
     * @var Image|MockObject
     */
    private Image $imageHelper;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface $urlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->context = $this->createMock(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->imageHelper = $this->createMock(Image::class);
        $this->urlBuilder = $this->createMock(UrlInterface::class);
    }

    /**
     * @return void
     */
    public function testPrepareDataSource(): void
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => 1,
                    ],
                ],
            ],
        ];
        $data = [
            'name' => 'test'
        ];
        $storeId = 1;
        $this->context->expects($this->once())
            ->method('getRequestParam')
            ->with('store')
            ->willReturn($storeId);
        $this->imageHelper->expects($this->exactly(2))->method('init')->willReturnSelf();
        $this->imageHelper->expects($this->exactly(2))->method('getUrl')->willReturn('http://example.com/images');
        $this->imageHelper->expects($this->once())->method('getLabel')->willReturn('label');
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('catalog/product/edit', ['id' => 1, 'store' => 1])
            ->willReturn('http://example.com/catalog/product/edit?id=1&store=1');
        $thumbnail = new Thumbnail(
            $this->context,
            $this->uiComponentFactory,
            $this->imageHelper,
            $this->urlBuilder,
            [],
            $data
        );

        $result = $thumbnail->prepareDataSource($dataSource);
        $this->assertEquals(
            [
                'data' => [
                    'items' => [
                        [
                            'entity_id' => 1,
                            'test_src' => 'http://example.com/images',
                            'test_alt' => 'label',
                            'test_link' => 'http://example.com/catalog/product/edit?id=1&store=1',
                            'test_orig_src' => 'http://example.com/images'
                        ]
                    ]
                ]
            ],
            $result
        );
    }
}
