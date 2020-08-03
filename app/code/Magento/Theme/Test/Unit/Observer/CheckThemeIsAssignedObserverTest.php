<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Observer\CheckThemeIsAssignedObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckThemeIsAssignedObserverTest extends TestCase
{
    /**
     * @var Customization|MockObject
     */
    protected $themeConfig;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventDispatcher;

    /**
     * @var CheckThemeIsAssignedObserver
     */
    protected $themeObserver;

    protected function setUp(): void
    {
        $this->themeConfig = $this->getMockBuilder(Customization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            CheckThemeIsAssignedObserver::class,
            [
                'themeConfig' => $this->themeConfig,
                'eventDispatcher' => $this->eventDispatcher,
            ]
        );
    }

    public function testCheckThemeIsAssigned()
    {
        $themeMock = $this->getMockBuilder(
            ThemeInterface::class
        )->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn($themeMock);

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->any())->method('isThemeAssignedToStore')->with($themeMock)->willReturn(true);

        $this->eventDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->with('assigned_theme_changed', ['theme' => $themeMock]);

        $result = $this->themeObserver->execute($observerMock);
        $this->assertNull($result);
    }
}
