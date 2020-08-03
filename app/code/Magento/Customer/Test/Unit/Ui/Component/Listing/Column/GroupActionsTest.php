<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Ui\Component\Listing\Column\GroupActions;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * Testing GroupAction grid column
 */
class GroupActionsTest extends TestCase
{
    /**
     * @var int
     */
    private const STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_ID = 0;

    /**
     * @var string
     */
    private const STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_NAME = 'Not Logged In';

    /**
     * @var int
     */
    private const STUB_GENERAL_CUSTOMER_GROUP_ID = 1;

    /**
     * @var string
     */
    private const STUB_GENERAL_CUSTOMER_GROUP_NAME = 'General';

    /**
     * @var string
     */
    private const STUB_GROUP_EDIT_URL = 'http://magento.com/customer/group/edit';

    /**
     * @var string
     */
    private const STUB_GROUP_DELETE_URL = 'http://magento.com/customer/group/delete';

    /**
     * @var GroupActions
     */
    private $component;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var GroupManagementInterface|MockObject
     */
    private $groupManagementMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->groupManagementMock = $this->getMockForAbstractClass(GroupManagementInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(
            UrlInterface::class,
            [],
            '',
            false
        );

        $this->component = $objectManager->getObject(
            GroupActions::class,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'urlBuilder' => $this->urlBuilderMock,
                'escaper' => $this->escaperMock,
                'components' => [],
                'data' => [
                    'name' => 'name'
                ],
                'groupManagement' => $this->groupManagementMock
            ]
        );
    }

    /**
     * Test data source with a non default customer group
     *
     * @dataProvider customerGroupsDataProvider
     *
     * @param array $items
     * @param bool $isDefaultGroup
     * @param array $expected
     */
    public function testPrepareDataSourceWithNonDefaultGroup(array $items, bool $isDefaultGroup, array $expected)
    {
        $dataSource = [
            'data' => [
                'items' => $items
            ]
        ];
        $expectedDataSource = [
            'data' => [
                'items' => $expected
            ]
        ];

        $this->groupManagementMock->expects($this->any())
            ->method('isReadonly')
            ->with(static::STUB_GENERAL_CUSTOMER_GROUP_ID)
            ->willReturn($isDefaultGroup);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->with(static::STUB_GENERAL_CUSTOMER_GROUP_NAME)
            ->willReturn(static::STUB_GENERAL_CUSTOMER_GROUP_NAME);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    [
                        'customer/group/edit',
                        [
                            'id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID
                        ],
                        static::STUB_GROUP_EDIT_URL],
                    [
                        'customer/group/delete',
                        [
                            'id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID
                        ],
                        static::STUB_GROUP_DELETE_URL
                    ]
                ]
            );

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * Test data source with a default customer group
     *
     * @dataProvider customerGroupsDataProvider
     */
    public function testPrepareDataSourceWithDefaultGroup()
    {
        $isDefaultGroup = true;
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'customer_group_id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                    ],
                    [
                        'customer_group_id' => static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_NAME,
                    ],
                ]
            ]
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'customer_group_id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                        'name' => [
                            'edit' => [
                                'href' => static::STUB_GROUP_EDIT_URL,
                                'label' => __('Edit'),
                            ]
                        ]
                    ],
                    [
                        'customer_group_id' => static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_NAME,
                        'name' => [
                            'edit' => [
                                'href' => static::STUB_GROUP_EDIT_URL,
                                'label' => __('Edit'),
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->groupManagementMock->expects($this->any())
            ->method('isReadonly')
            ->willReturn($isDefaultGroup);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnMap(
                [
                    [
                        static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                        null,
                        static::STUB_GENERAL_CUSTOMER_GROUP_NAME
                    ],
                    [
                        static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_NAME,
                        null,
                        static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_NAME
                    ]
                ]
            );
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    [
                        'customer/group/edit',
                        [
                            'id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID
                        ],
                        static::STUB_GROUP_EDIT_URL
                    ],
                    [
                        'customer/group/edit',
                        [
                            'id' => static::STUB_NOT_LOGGED_IN_CUSTOMER_GROUP_ID
                        ],
                        static::STUB_GROUP_EDIT_URL
                    ]
                ]
            );

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * Providing customer group data
     *
     * @return array
     */
    public function customerGroupsDataProvider(): array
    {
        return [
            [
                [
                    [
                        'customer_group_id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                    ],
                ],
                false,
                [
                    [
                        'customer_group_id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID,
                        'customer_group_code' => static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                        'name' => [
                            'edit' => [
                                'href' => static::STUB_GROUP_EDIT_URL,
                                'label' => __('Edit'),
                            ],
                            'delete' => [
                                'href' => static::STUB_GROUP_DELETE_URL,
                                'label' => __('Delete'),
                                'post' => true,
                                'confirm' => [
                                    'title' => __('Delete %1', 'General'),
                                    'message' => __(
                                        'Are you sure you want to delete a %1 record?',
                                        'General'
                                    )
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
