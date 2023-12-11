<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column;

use Magento\Cms\Ui\Component\Listing\Column\PageActions;
use Magento\Cms\ViewModel\Page\Grid\UrlBuilder;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Cms\Ui\Component\Listing\Column\PageActions class.
 */
class PageActionsTest extends TestCase
{

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var UrlBuilder|MockObject
     */
    private $scopeUrlBuilderMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var PageActions
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->scopeUrlBuilderMock = $this->createMock(UrlBuilder::class);
        $this->processorMock = $this->createMock(Processor::class);
        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();

        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            PageActions::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'context' => $this->contextMock,
                'scopeUrlBuilder' => $this->scopeUrlBuilderMock
            ]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'escaper', $this->escaperMock);
    }

    /**
     * Verify Prepare Items by page Id.
     *
     * @dataProvider configDataProvider
     * @param int $pageId
     * @param string $title
     * @param string $name
     * @param array $items
     * @param array $expectedItems
     * @return void
     */
    public function testPrepareItemsByPageId(
        int $pageId,
        string $title,
        string $name,
        array $items,
        array $expectedItems
    ):void {
        $this->contextMock->expects($this->never())
            ->method('getProcessor')
            ->willReturn($this->processorMock);
        $this->escaperMock->expects(static::once())
            ->method('escapeHtml')
            ->with($title)
            ->willReturn($title);
        // Configure mocks and object data
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    [
                        PageActions::CMS_URL_PATH_EDIT,
                        [
                            'page_id' => $pageId
                        ],
                        'test/url/edit',
                    ],
                    [
                        PageActions::CMS_URL_PATH_DELETE,
                        [
                            'page_id' => $pageId
                        ],
                        'test/url/delete',
                    ],
                ]
            );

        $this->scopeUrlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturn('test/url/view');

        $this->model->setName($name);
        $items = $this->model->prepareDataSource($items);
        // Run test
        $this->assertEquals($expectedItems, $items['data']['items']);
    }

    /**
     * Data provider for testPrepareItemsByPageId
     *
     * @return array
     */
    public function configDataProvider():array
    {
        $pageId = 1;
        $title = 'page title';
        $identifier = 'page_identifier';
        $name = 'item_name';

        return [
            [
                'pageId' => $pageId,
                'title' => $title,
                'name' => $name,
                'items' => [
                    'data' => [
                        'items' => [
                            [
                                'page_id' => $pageId,
                                'title' => $title,
                                'identifier' => $identifier
                            ]
                        ]
                    ]
                ],
                'expectedItems' => [
                    [
                        'page_id' => $pageId,
                        'title' => $title,
                        'identifier' => $identifier,
                        $name => [
                            'edit' => [
                                'href' => 'test/url/edit',
                                'label' => __('Edit'),
                            ],
                            'delete' => [
                                'href' => 'test/url/delete',
                                'label' => __('Delete'),
                                'confirm' => [
                                    'title' => __('Delete %1', $title),
                                    'message' => __('Are you sure you want to delete a %1 record?', $title),
                                ],
                                'post' => true,
                            ],
                            'preview' => [
                                'href' => 'test/url/view',
                                'label' => __('View'),
                                'target' => '_blank'
                            ]
                        ],
                    ],
                ]
            ]
        ];
    }
}
