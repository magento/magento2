<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Observer;

class CheckThemeIsAssignedObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Config\Customization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \Magento\Theme\Observer\CheckThemeIsAssignedObserver
     */
    protected $themeObserver;

    protected function setUp()
    {
        $this->themeConfig = $this->getMockBuilder('Magento\Theme\Model\Config\Customization')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            'Magento\Theme\Observer\CheckThemeIsAssignedObserver',
            [
                'themeConfig' => $this->themeConfig,
                'eventDispatcher' => $this->eventDispatcher,
            ]
        );
    }

    public function testCheckThemeIsAssigned()
    {
        $themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder('Magento\Framework\Event')->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn($themeMock);

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->any())->method('isThemeAssignedToStore')->with($themeMock)->willReturn(true);

        $this->eventDispatcher
            ->expects($this->any())
            ->method('dispatch')
            ->with('assigned_theme_changed', ['theme' => $themeMock]);

        $this->themeObserver->execute($observerMock);
    }
}
