<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Product;

use Magento\Catalog\Ui\Component\Product\MassAction;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class MassActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationMock;

    /**
     * @var MassAction
     */
    private $massAction;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMockForAbstractClass();

        $this->massAction = $this->objectManager->getObject(
            MassAction::class,
            [
                'authorization' => $this->authorizationMock,
                'context' => $this->contextMock,
                'data' => []
            ]
        );
    }

    public function testGetComponentName()
    {
        $this->assertTrue($this->massAction->getComponentName() === MassAction::NAME);
    }

    /**
     * @param string $componentName
     * @param array $componentData
     * @param bool $isAllowed
     * @param bool $expectActionConfig
     * @return void
     * @dataProvider getPrepareDataProvider
     */
    public function testPrepare($componentName, $componentData, $isAllowed = true, $expectActionConfig = true)
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        /** @var \Magento\Ui\Component\MassAction $action */
        $action = $this->objectManager->getObject(
            \Magento\Ui\Component\MassAction::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'name' => $componentName,
                    'config' => $componentData,
                ]
            ]
        );
        $this->authorizationMock->method('isAllowed')
            ->willReturn($isAllowed);
        $this->massAction->addComponent('action', $action);
        $this->massAction->prepare();
        $expected = $expectActionConfig ? ['actions' => [$action->getConfiguration()]] : [];
        $this->assertEquals($expected, $this->massAction->getConfiguration());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPrepareDataProvider() : array
    {
        return [
            [
                'test_component1',
                [
                    'type' => 'first_action',
                    'label' => 'First Action',
                    'url' => '/module/controller/firstAction'
                ],
            ],
            [
                'test_component2',
                [
                    'type' => 'second_action',
                    'label' => 'Second Action',
                    'actions' => [
                        [
                            'type' => 'second_sub_action1',
                            'label' => 'Second Sub Action 1',
                            'url' => '/module/controller/secondSubAction1'
                        ],
                        [
                            'type' => 'second_sub_action2',
                            'label' => 'Second Sub Action 2',
                            'url' => '/module/controller/secondSubAction2'
                        ],
                    ]
                ],
            ],
            [
                'status_component',
                [
                    'type' => 'status',
                    'label' => 'Status',
                    'actions' => [
                        [
                            'type' => 'enable',
                            'label' => 'Second Sub Action 1',
                            'url' => '/module/controller/enable'
                        ],
                        [
                            'type' => 'disable',
                            'label' => 'Second Sub Action 2',
                            'url' => '/module/controller/disable'
                        ],
                    ]
                ],
            ],
            [
                'status_component_not_allowed',
                [
                    'type' => 'status',
                    'label' => 'Status',
                    'actions' => [
                        [
                            'type' => 'enable',
                            'label' => 'Second Sub Action 1',
                            'url' => '/module/controller/enable'
                        ],
                        [
                            'type' => 'disable',
                            'label' => 'Second Sub Action 2',
                            'url' => '/module/controller/disable'
                        ],
                    ]
                ],
                false,
                false
            ],
            [
                'delete_component',
                [
                    'type' => 'delete',
                    'label' => 'First Action',
                    'url' => '/module/controller/delete'
                ],
            ],
            [
                'delete_component_not_allowed',
                [
                    'type' => 'delete',
                    'label' => 'First Action',
                    'url' => '/module/controller/delete'
                ],
                false,
                false
            ],
            [
                'attributes_component',
                [
                    'type' => 'delete',
                    'label' => 'First Action',
                    'url' => '/module/controller/attributes'
                ],
            ],
            [
                'attributes_component_not_allowed',
                [
                    'type' => 'delete',
                    'label' => 'First Action',
                    'url' => '/module/controller/attributes'
                ],
                false,
                false
            ],
        ];
    }

    /**
     * @param bool $expected
     * @param string $actionType
     * @param int $callNum
     * @param string $resource
     * @param bool $isAllowed
     * @dataProvider isActionAllowedDataProvider
     */
    public function testIsActionAllowed($expected, $actionType, $callNum, $resource = '', $isAllowed = true)
    {
        $this->authorizationMock->expects($this->exactly($callNum))
            ->method('isAllowed')
            ->with($resource)
            ->willReturn($isAllowed);

        $this->assertEquals($expected, $this->massAction->isActionAllowed($actionType));
    }

    public function isActionAllowedDataProvider()
    {
        return [
            'other' => [true, 'other', 0,],
            'delete-allowed' => [true, 'delete', 1, 'Magento_Catalog::products'],
            'delete-not-allowed' => [false, 'delete', 1, 'Magento_Catalog::products', false],
            'status-allowed' => [true, 'status', 1, 'Magento_Catalog::products'],
            'status-not-allowed' => [false, 'status', 1, 'Magento_Catalog::products', false],
            'attributes-allowed' => [true, 'attributes', 1, 'Magento_Catalog::update_attributes'],
            'attributes-not-allowed' => [false, 'attributes', 1, 'Magento_Catalog::update_attributes', false],

        ];
    }
}
