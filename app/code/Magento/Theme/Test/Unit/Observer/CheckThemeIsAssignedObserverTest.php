<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Observer;

class CheckThemeIsAssignedObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Config\Customization|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \Magento\Theme\Observer\CheckThemeIsAssignedObserver
     */
    protected $themeObserver;

    protected function setUp(): void
    {
        $this->themeConfig = $this->getMockBuilder(\Magento\Theme\Model\Config\Customization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            \Magento\Theme\Observer\CheckThemeIsAssignedObserver::class,
            [
                'themeConfig' => $this->themeConfig,
                'eventDispatcher' => $this->eventDispatcher,
            ]
        );
    }

    public function testCheckThemeIsAssigned()
    {
        $themeMock = $this->getMockBuilder(
            \Magento\Framework\View\Design\ThemeInterface::class
        )->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn($themeMock);

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
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
