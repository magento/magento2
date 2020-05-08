<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\ImageFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Config\Customization;
use Magento\Theme\Observer\CleanThemeRelatedContentObserver;
use Magento\Widget\Model\ResourceModel\Layout\Update\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CleanThemeRelatedContentObserverTest extends TestCase
{
    /**
     * @var Customization|MockObject
     */
    protected $themeConfig;

    /**
     * @var ImageFactory|MockObject
     */
    protected $themeImageFactory;

    /**
     * @var Collection|MockObject
     */
    protected $updateCollection;

    /**
     * @var CleanThemeRelatedContentObserver
     */
    protected $themeObserver;

    protected function setUp(): void
    {
        $this->themeConfig = $this->getMockBuilder(Customization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeImageFactory = $this->getMockBuilder(ImageFactory::class)
            ->setMethods(['create', 'removePreviewImage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateCollection = $this->getMockBuilder(
            Collection::class
        )->setMethods(['addThemeFilter', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            CleanThemeRelatedContentObserver::class,
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

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Theme isn\'t deletable.');
        $this->themeObserver->execute($observerMock);
    }

    public function testCleanThemeRelatedContentNonObjectTheme()
    {
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn('Theme as a string');

        $observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->never())->method('isThemeAssignedToStore');

        $this->themeObserver->execute($observerMock);
    }
}
