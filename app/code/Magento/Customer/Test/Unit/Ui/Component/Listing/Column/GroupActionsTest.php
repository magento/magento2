<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);
        $this->urlBuilderMock = $this->createMock(
            UrlInterface::class
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
     * @param array $dataSource
     * @param bool $isDefaultGroup
     * @param array $expectedDataSource
     */
    #[DataProvider('customerGroupsDataProvider')]
    public function testPrepareDataSourceWithNonDefaultGroup(
        array $dataSource,
        bool $isDefaultGroup,
        array $expectedDataSource
    ): void {
        $this->groupManagementMock->expects($this->any())
            ->method('isReadonly')
            ->willReturn($isDefaultGroup);
        $this->escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->willReturnCallback(function ($value) {
                return $value;
            });
        $this->urlBuilderMock->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(function ($route) {
                if ($route === 'customer/group/edit') {
                    return static::STUB_GROUP_EDIT_URL;
                }
                if ($route === 'customer/group/delete') {
                    return static::STUB_GROUP_DELETE_URL;
                }
                return null;
            });

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * Test data source with a default customer group
     *
     * @param array $dataSource
     * @param bool $isDefaultGroup
     * @param array $expectedDataSource
     */
    #[DataProvider('customerGroupsDataProvider')]
    public function testPrepareDataSourceWithDefaultGroup(
        array $dataSource,
        bool $isDefaultGroup,
        array $expectedDataSource
    ): void {
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
            ->willReturnCallback(function ($route) {
                if ($route === 'customer/group/edit') {
                    return static::STUB_GROUP_EDIT_URL;
                }
                if ($route === 'customer/group/delete') {
                    return static::STUB_GROUP_DELETE_URL;
                }
                return null;
            });

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }

    /**
     * Providing customer group data
     *
     * @return array
     */
    public static function customerGroupsDataProvider(): array
    {
        return [
            [
                [
                    'data' => [
                        'items' => [
                            [
                                'customer_group_id' => static::STUB_GENERAL_CUSTOMER_GROUP_ID,
                                'customer_group_code' => static::STUB_GENERAL_CUSTOMER_GROUP_NAME,
                            ],
                        ]
                    ]
                ],
                false,
                [
                    'data' => [
                        'items' => [
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
                ]
            ]
        ];
    }
}
