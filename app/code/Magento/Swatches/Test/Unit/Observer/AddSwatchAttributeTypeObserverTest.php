<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Observer;

/**
 * Observer test
 */
class AddSwatchAttributeTypeObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleManagerMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventObserverMock;

    /** @var \Magento\Swatches\Observer\AddSwatchAttributeTypeObserver|\PHPUnit_Framework_MockObject_MockObject */
    protected $observerMock;

    protected function setUp()
    {
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);

        $this->eventObserverMock = $this->createPartialMock(
            \Magento\Framework\Event\Observer::class,
            ['getForm', 'getEvent', 'getAttribute']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            \Magento\Swatches\Observer\AddSwatchAttributeTypeObserver::class,
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

        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getResponse']);
        $this->eventObserverMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getEvent')
            ->willReturn($eventMock);

        $response = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getTypes']);
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
