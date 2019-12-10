<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column;

use Magento\Cms\Ui\Component\Listing\Column\PageActions;
use Magento\Framework\Escaper;

/**
 * Test for Magento\Cms\Ui\Component\Listing\Column\PageActions class.
 */
class PageActionsTest extends \PHPUnit\Framework\TestCase
{
    public function testPrepareItemsByPageId()
    {
        $pageId = 1;
        // Create Mocks and SUT
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlBuilderMock */
        $urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->never())->method('getProcessor')->willReturn($processor);

        /** @var \Magento\Cms\Ui\Component\Listing\Column\PageActions $model */
        $model = $objectManager->getObject(
            \Magento\Cms\Ui\Component\Listing\Column\PageActions::class,
            [
                'urlBuilder' => $urlBuilderMock,
                'context' => $contextMock,
            ]
        );

        $escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
        $objectManager->setBackwardCompatibleProperty($model, 'escaper', $escaper);

        // Define test input and expectations
        $title = 'page title';
        $items = [
            'data' => [
                'items' => [
                    [
                        'page_id' => $pageId,
                        'title' => $title
                    ]
                ]
            ]
        ];
        $name = 'item_name';
        $expectedItems = [
            [
                'page_id' => $pageId,
                'title' => $title,
                $name => [
                    'edit' => [
                        'href' => 'test/url/edit',
                        'label' => __('Edit'),
                        '__disableTmpl' => true,
                    ],
                    'delete' => [
                        'href' => 'test/url/delete',
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title),
                            '__disableTmpl' => true,
                        ],
                        'post' => true,
                        '__disableTmpl' => true,
                    ],
                ],
            ],
        ];

        $escaper->expects(static::once())
            ->method('escapeHtml')
            ->with($title)
            ->willReturn($title);
        // Configure mocks and object data
        $urlBuilderMock->expects($this->any())
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
        $model->setName($name);
        $items = $model->prepareDataSource($items);
        // Run test
        $this->assertEquals($expectedItems, $items['data']['items']);
    }
}
