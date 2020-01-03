<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
 * Class GroupActionsTest
 *
 * Testing GroupAction grid column
 */
class GroupActionsTest extends TestCase
{
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
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);
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
        $customerGroup = 'General';
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
            ->with(1)
            ->willReturn($isDefaultGroup);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->with($customerGroup)
            ->willReturn($customerGroup);
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['customer/group/edit', ['id' => 1], 'http://magento.com/customer/group/edit'],
                    ['customer/group/delete', ['id' => 1], 'http://magento.com/customer/group/delete']
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
                        'customer_group_id' => 1,
                        'customer_group_code' => 'General',
                    ],
                    [
                        'customer_group_id' => 0,
                        'customer_group_code' => 'Not Logged In',
                    ],
                ]
            ]
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'customer_group_id' => 1,
                        'customer_group_code' => 'General',
                        'name' => [
                            'edit' => [
                                'href' => 'http://magento.com/customer/group/edit',
                                'label' => __('Edit'),
                                '__disableTmpl' => true,
                            ]
                        ]
                    ],
                    [
                        'customer_group_id' => 0,
                        'customer_group_code' => 'Not Logged In',
                        'name' => [
                            'edit' => [
                                'href' => 'http://magento.com/customer/group/edit',
                                'label' => __('Edit'),
                                '__disableTmpl' => true,
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
                    ['General', null, 'General'],
                    ['Not Logged In', null, 'Not Logged In']
                ]
            );
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnMap(
                [
                    ['customer/group/edit', ['id' => 1], 'http://magento.com/customer/group/edit'],
                    ['customer/group/edit', ['id' => 0], 'http://magento.com/customer/group/edit']
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
                        'customer_group_id' => 1,
                        'customer_group_code' => 'General',
                    ],
                ],
                false,
                [
                    [
                        'customer_group_id' => 1,
                        'customer_group_code' => 'General',
                        'name' => [
                            'edit' => [
                                'href' => 'http://magento.com/customer/group/edit',
                                'label' => __('Edit'),
                                '__disableTmpl' => true,
                            ],
                            'delete' => [
                                'href' => 'http://magento.com/customer/group/delete',
                                'label' => __('Delete'),
                                'post' => true,
                                '__disableTmpl' => true,
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
