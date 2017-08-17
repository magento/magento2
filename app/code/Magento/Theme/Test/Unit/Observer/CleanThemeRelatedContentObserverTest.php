<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Observer;

class CleanThemeRelatedContentObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Config\Customization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeImageFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Layout\Update\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateCollection;

    /**
     * @var \Magento\Theme\Observer\CleanThemeRelatedContentObserver
     */
    protected $themeObserver;

    protected function setUp()
    {
        $this->themeConfig = $this->getMockBuilder(\Magento\Theme\Model\Config\Customization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeImageFactory = $this->getMockBuilder(\Magento\Framework\View\Design\Theme\ImageFactory::class)
            ->setMethods(['create', 'removePreviewImage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateCollection = $this->getMockBuilder(
            \Magento\Widget\Model\ResourceModel\Layout\Update\Collection::class
        )->setMethods(['addThemeFilter', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            \Magento\Theme\Observer\CleanThemeRelatedContentObserver::class,
            [
                'themeConfig' => $this->themeConfig,
                'themeImageFactory' => $this->themeImageFactory,
                'updateCollection' => $this->updateCollection,
            ]
        );
    }

    public function testCleanThemeRelatedContent()
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

        $this->themeConfig
            ->expects($this->any())
            ->method('isThemeAssignedToStore')
            ->with($themeMock)
            ->willReturn(false);

        $this->themeImageFactory
            ->expects($this->once())
            ->method('create')
            ->with(['theme' => $themeMock])
            ->willReturnSelf();
        $this->themeImageFactory->expects($this->once())->method('removePreviewImage');

        $this->updateCollection->expects($this->once())->method('addThemeFilter')->willReturnSelf();
        $this->updateCollection->expects($this->once())->method('delete');

        $this->themeObserver->execute($observerMock);
    }

    public function testCleanThemeRelatedContentException()
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

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class, 'Theme isn\'t deletable.');
        $this->themeObserver->execute($observerMock);
    }

    public function testCleanThemeRelatedContentNonObjectTheme()
    {
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn('Theme as a string');

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->never())->method('isThemeAssignedToStore');

        $this->themeObserver->execute($observerMock);
    }
}
