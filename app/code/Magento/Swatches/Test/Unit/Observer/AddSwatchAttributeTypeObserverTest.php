<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Observer;

/**
 * Observer test
 */
class AddSwatchAttributeTypeObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleManagerMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventObserverMock;

    /** @var \Magento\Swatches\Observer\AddSwatchAttributeTypeObserver|\PHPUnit_Framework_MockObject_MockObject */
    protected $observerMock;

    public function setUp()
    {
        $this->moduleManagerMock = $this->getMock(
            '\Magento\Framework\Module\Manager',
            [],
            [],
            '',
            false
        );

        $this->eventObserverMock = $this->getMock(
            '\Magento\Framework\Event\Observer',
            ['getForm', 'getEvent', 'getAttribute'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            'Magento\Swatches\Observer\AddSwatchAttributeTypeObserver',
            [
                'moduleManager' => $this->moduleManagerMock,
            ]
        );
    }

    /**
     * @dataProvider dataAddSwatch
     */
    public function testAddSwatchAttributeType($exp)
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isOutputEnabled')
            ->willReturn($exp['isOutputEnabled']);

        $eventMock = $this->getMock('\Magento\Framework\Event', ['getResponse'], [], '', false);
        $this->eventObserverMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getEvent')
            ->willReturn($eventMock);

        $response = $this->getMock('\Magento\Framework\DataObject', ['getTypes'], [], '', false);
        $eventMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getResponse')
            ->willReturn($response);

        $response
            ->expects($this->exactly($exp['methods_count']))
            ->method('getTypes')
            ->willReturn($exp['outputArray']);

        $this->observerMock->execute($this->eventObserverMock);
    }

    public function dataAddSwatch()
    {
        return [
            [
                [
                    'isOutputEnabled' => true,
                    'methods_count' => 1,
                    'outputArray' => []
                ]
            ],
            [
                [
                    'isOutputEnabled' => false,
                    'methods_count' => 0,
                    'outputArray' => []
                ]
            ],
        ];
    }
}
