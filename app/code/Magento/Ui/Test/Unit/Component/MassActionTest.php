<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\MassAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MassActionTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        /** @var MassAction $massAction */
        $massAction = $this->objectManager->getObject(
            MassAction::class,
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );

        $this->assertSame(MassAction::NAME, $massAction->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @param string $componentName
     * @param array $componentData
     * @return void
     * @dataProvider getPrepareDataProvider
     */
    public function testPrepare($componentName, $componentData)
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        /** @var \Magento\Ui\Component\Action $action */
        $action = $this->objectManager->getObject(
            MassAction::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'name' => $componentName,
                    'config' => $componentData,
                ]
            ]
        );
        /** @var MassAction $massAction */
        $massAction = $this->objectManager->getObject(
            MassAction::class,
            [
                'context' => $this->contextMock,
                'data' => []
            ]
        );
        $massAction->addComponent('action', $action);
        $massAction->prepare();
        $this->assertEquals(['actions' => [$action->getConfiguration()]], $massAction->getConfiguration());
    }

    /**
     * @return array
     */
    public static function getPrepareDataProvider()
    {
        return [
            [
                'test_component1',
                [
                    'type' => 'first_action',
                    'label' => 'First Action',
                    'url' => '/module/controller/firstAction',
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
                    ],
                ],
            ],
        ];
    }
}
