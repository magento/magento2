<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component;

use Magento\Ui\Component\MassAction;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class MassActionTest
 */
class MassActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->getMockForAbstractClass();
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        /** @var MassAction $massAction */
        $massAction = $this->objectManager->getObject(
            'Magento\Ui\Component\MassAction',
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertTrue($massAction->getComponentName() === MassAction::NAME);
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        $this->contextMock->expects($this->at(0))
            ->method('getUrl')
            ->with('/module/controller/firstAction', []);
        $this->contextMock->expects($this->at(1))
            ->method('getUrl')
            ->with('/module/controller/secondSubAction1', []);
        $this->contextMock->expects($this->at(2))
            ->method('getUrl')
            ->with('/module/controller/secondSubAction2', []);
        /** @var MassAction $massAction */
        $massAction = $this->objectManager->getObject(
            'Magento\Ui\Component\MassAction',
            [
                'context' => $this->contextMock,
                'data' => [
                    'config' => [
                        'actions' => [
                            [
                                'type' => 'first_action',
                                'label' => 'First Action',
                                'url' => '/module/controller/firstAction'
                            ],
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
                        ]
                    ]

                ]
            ]
        );
        $massAction->prepare();
    }

    /**
     * Run test prepare method with dynamic actions
     *
     * @return void
     */
    public function testPrepareWithDynamicActions()
    {
        $this->contextMock->expects($this->at(2))
            ->method('getUrl')
            ->with('/module/controller/firstAction', []);
        $options = $this->getMockBuilder('Magento\Framework\Data\OptionSourceInterface')->getMockForAbstractClass();
        $options->expects($this->any())
            ->method('toOptionArray')
            ->willReturn([
                'second_action' => [
                    [
                        'type' => 'second_sub_action1',
                        'label' => 'Second Sub Action 1',
                        'url' => ['param' => '21']
                    ],
                    [
                        'type' => 'second_sub_action2',
                        'label' => 'Second Sub Action 2',
                        'url' => ['param' => '22']
                    ],
                ]
            ]);
        $this->contextMock->expects($this->at(0))
            ->method('getUrl')
            ->with('/module/controller/secondSubAction', ['param' => '21']);
        $this->contextMock->expects($this->at(1))
            ->method('getUrl')
            ->with('/module/controller/secondSubAction', ['param' => '22']);
        /** @var MassAction $massAction */
        $massAction = $this->objectManager->getObject(
            'Magento\Ui\Component\MassAction',
            [
                'context' => $this->contextMock,
                'data' => [
                    'config' => [
                        'actions' => [
                            'first_action' => [
                                'type' => 'first_action',
                                'label' => 'First Action',
                                'url' => '/module/controller/firstAction'
                            ],
                            'second_action' => [
                                'type' => 'second_action',
                                'label' => 'Second Action',
                                'url' => '/module/controller/secondSubAction'
                            ],
                        ]
                    ]

                ],
                'optionsProvider' => $options,
            ]
        );
        $massAction->prepare();
    }
}
