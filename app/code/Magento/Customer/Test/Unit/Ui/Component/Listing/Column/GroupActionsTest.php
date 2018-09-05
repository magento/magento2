<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\GroupActions;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * GroupActionsTest contains unit tests for \Magento\Customer\Ui\Component\Listing\Column\GroupActions class
 */
class GroupActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupActions
     */
    protected $groupActions;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $context = $this->createMock(ContextInterface::class);

        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects(static::never())
            ->method('getProcessor')
            ->willReturn($processor);

        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();

        $this->groupActions = $objectManager->getObject(GroupActions::class, [
            'context' => $context,
            'urlBuilder' => $this->urlBuilder,
            'escaper' => $this->escaper,
        ]);
    }

    /**
     * @covers \Magento\Customer\Ui\Component\Listing\Column\GroupActions::prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $groupId = 1;
        $groupCode = 'group code';
        $items = [
            'data' => [
                'items' => [
                    [
                        'customer_group_id' => $groupId,
                        'customer_group_code' => $groupCode
                    ]
                ]
            ]
        ];
        $name = 'item_name';
        $expectedItems = [
            [
                'customer_group_id' => $groupId,
                'customer_group_code' => $groupCode,
                $name => [
                    'edit' => [
                        'href' => 'test/url/edit',
                        'label' => __('Edit'),
                    ],
                    'delete' => [
                        'href' => 'test/url/delete',
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $groupCode),
                            'message' => __('Are you sure you want to delete a %1 record?', $groupCode)
                        ],
                    ]
                ],
            ]
        ];

        $this->escaper->expects(static::once())
            ->method('escapeHtml')
            ->with($groupCode)
            ->willReturn($groupCode);

        $this->urlBuilder->expects(static::exactly(2))
            ->method('getUrl')
            ->willReturnMap(
                [
                    [
                        GroupActions::URL_PATH_EDIT,
                        [
                            'id' => $groupId
                        ],
                        'test/url/edit',
                    ],
                    [
                        GroupActions::URL_PATH_DELETE,
                        [
                            'id' => $groupId
                        ],
                        'test/url/delete',
                    ],
                ]
            );

        $this->groupActions->setData('name', $name);

        $actual = $this->groupActions->prepareDataSource($items);
        static::assertEquals($expectedItems, $actual['data']['items']);
    }
}
