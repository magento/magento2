<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\ProductActions;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductActionsTest extends TestCase
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
        $this->urlBuilder = $this->createMock(UrlInterface::class);
    }

    /**
     * @return void
     */
    public function testPrepareDataSource(): void
    {
        $storeId = 1;
        $data = [
            'name' => 'test'
        ];
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => 1,
                    ],
                ],
            ],
        ];

        $this->context->expects($this->once())
            ->method('getFilterParam')
            ->with('store_id')
            ->willReturn($storeId);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('catalog/product/edit', ['id' => 1, 'store' => 1])
            ->willReturn('http://example.com/catalog/product/edit?id=1&store=1');

        $productActions = new ProductActions(
            $this->context,
            $this->uiComponentFactory,
            $this->urlBuilder,
            [],
            $data
        );

        $result = $productActions->prepareDataSource($dataSource);

        $this->assertEquals(
            [
                'data' => [
                    'items' => [
                        [
                            'entity_id' => 1,
                            'test' => [
                                'edit' => [
                                    'href' => 'http://example.com/catalog/product/edit?id=1&store=1',
                                    'ariaLabel' => 'Edit ',
                                    'label' => __('Edit'),
                                    'hidden' => false
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $result
        );
    }
}
