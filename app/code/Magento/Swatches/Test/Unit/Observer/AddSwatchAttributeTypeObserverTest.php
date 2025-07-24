<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Observer\AddSwatchAttributeTypeObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Observer test
 */
class AddSwatchAttributeTypeObserverTest extends TestCase
{
    /** @var Manager|MockObject */
    protected $moduleManagerMock;

    /** @var Observer|MockObject */
    protected $eventObserverMock;

    /** @var AddSwatchAttributeTypeObserver|MockObject */
    protected $observerMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(Manager::class);

        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getForm', 'getAttribute'])
            ->onlyMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            AddSwatchAttributeTypeObserver::class,
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

        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverMock
            ->expects($this->exactly($exp['methods_count']))
            ->method('getEvent')
            ->willReturn($eventMock);

        $response = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getTypes'])
            ->disableOriginalConstructor()
            ->getMock();
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

    /**
     * @return array
     */
    public static function dataAddSwatch()
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
