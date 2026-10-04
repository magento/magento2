<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Observer\AddSwatchAttributeTypeObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Observer test
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class AddSwatchAttributeTypeObserverTest extends TestCase
{
    use MockCreationTrait;
    /** @var Manager|MockObject */
    protected $moduleManagerMock;

    /** @var Observer|MockObject */
    protected $eventObserverMock;

    /** @var AddSwatchAttributeTypeObserver|MockObject */
    protected $observerMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(Manager::class);

        $this->eventObserverMock = $this->createPartialMockWithReflection(Observer::class, ['getEvent']);
        $objectManager = new ObjectManager($this);
        $this->observerMock = $objectManager->getObject(
            AddSwatchAttributeTypeObserver::class,
            [
                'moduleManager' => $this->moduleManagerMock,
            ]
        );
    }

    #[DataProvider('dataAddSwatch')]
    public function testAddSwatchAttributeType($exp)
    {
        $this->moduleManagerMock
            ->expects($this->once())
            ->method('isOutputEnabled')
            ->willReturn($exp['isOutputEnabled']);

        $response = $this->createPartialMockWithReflection(DataObject::class, ['getTypes', 'setTypes']);
        $response->method('getTypes')->willReturn($exp['outputArray']);

        $eventMock = $this->createPartialMockWithReflection(Event::class, ['getResponse']);
        $eventMock->expects($this->exactly($exp['methods_count']))
            ->method('getResponse')
            ->willReturn($response);

        $this->eventObserverMock->method('getEvent')->willReturn($eventMock);

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
