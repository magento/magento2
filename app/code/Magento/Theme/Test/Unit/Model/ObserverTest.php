<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeCustomization;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetRepo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetsMock;

    /**
     * @var \Magento\Theme\Model\Config\Customization|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeConfig;

    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeImageFactory;

    /**
     * @var \Magento\Widget\Model\Resource\Layout\Update\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateCollection;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Theme\Model\Theme\Registration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registration;

    /**
     * @var \Magento\Theme\Model\Observer
     */
    protected $themeObserver;

    protected function setUp()
    {
        $this->themeCustomization = $this->getMock(
            'Magento\Framework\View\Design\Theme\Customization',
            [],
            [],
            '',
            false
        );
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getCustomization'],
            [],
            '',
            false
        );
        $themeMock->expects(
            $this->any()
        )->method(
            'getCustomization'
        )->will(
            $this->returnValue($this->themeCustomization)
        );

        $designMock = $this->getMock('Magento\Framework\View\DesignInterface');
        $designMock->expects($this->any())->method('getDesignTheme')->will($this->returnValue($themeMock));

        $this->assetsMock = $this->getMock(
            'Magento\Framework\View\Asset\GroupedCollection',
            [],
            [],
            '',
            false,
            false
        );
        $this->_configMock = $this->getMock(
            '\Magento\Framework\App\Config\ReinitableConfigInterface',
            [],
            [],
            '',
            false,
            false
        );

        $this->assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);

        $this->themeConfig = $this->getMockBuilder('Magento\Theme\Model\Config\Customization')
            ->disableOriginalConstructor()
            ->getMock();

        $this->themeImageFactory = $this->getMockBuilder('Magento\Framework\View\Design\Theme\ImageFactory')
            ->setMethods(['create', 'removePreviewImage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateCollection = $this->getMockBuilder('Magento\Widget\Model\Resource\Layout\Update\Collection')
            ->setMethods(['addThemeFilter', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventDispatcher = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->registration = $this->getMockBuilder('Magento\Theme\Model\Theme\Registration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->themeObserver = $objectManagerHelper->getObject(
            'Magento\Theme\Model\Observer',
            [
                'design' => $designMock,
                'assets' => $this->assetsMock,
                'assetRepo' => $this->assetRepo,
                'themeConfig' => $this->themeConfig,
                'eventDispatcher' => $this->eventDispatcher,
                'themeImageFactory' => $this->themeImageFactory,
                'updateCollection' => $this->updateCollection,
                'registration' => $this->registration,
                'logger' => $this->logger,
            ]
        );
    }

    public function testApplyThemeCustomization()
    {
        $asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $file = $this->getMock('Magento\Theme\Model\Theme\File', [], [], '', false);
        $fileService = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Design\Theme\Customization\FileAssetInterface'
        );
        $file->expects($this->any())->method('getCustomizationService')->will($this->returnValue($fileService));

        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->will($this->returnValue($asset));

        $this->themeCustomization->expects($this->once())->method('getFiles')->will($this->returnValue([$file]));
        $this->assetsMock->expects($this->once())->method('add')->with($this->anything(), $asset);

        $observer = new \Magento\Framework\Event\Observer();
        $this->themeObserver->applyThemeCustomization($observer);
    }

    public function testApplyThemeCustomizationException()
    {
        $file = $this->getMock('Magento\Theme\Model\Theme\File', [], [], '', false);
        $file->expects($this->any())
            ->method('getCustomizationService')
            ->willThrowException(new \InvalidArgumentException());

        $this->themeCustomization->expects($this->once())->method('getFiles')->will($this->returnValue([$file]));
        $this->logger->expects($this->once())->method('critical');

        $observer = new \Magento\Framework\Event\Observer();
        $this->themeObserver->applyThemeCustomization($observer);
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

        $this->themeObserver->checkThemeIsAssigned($observerMock);
    }

    public function testCleanThemeRelatedContent()
    {
        $themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder('Magento\Framework\Event')->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn($themeMock);

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
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

        $this->themeObserver->cleanThemeRelatedContent($observerMock);
    }

    public function testCleanThemeRelatedContentException()
    {
        $themeMock = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMockForAbstractClass();

        $eventMock = $this->getMockBuilder('Magento\Framework\Event')->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn($themeMock);

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->any())->method('isThemeAssignedToStore')->with($themeMock)->willReturn(true);


        $this->setExpectedException('Magento\Framework\Exception\LocalizedException', 'Theme isn\'t deletable.');
        $this->themeObserver->cleanThemeRelatedContent($observerMock);
    }

    public function testCleanThemeRelatedContentNonObjectTheme()
    {
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')->disableOriginalConstructor()->getMock();
        $eventMock->expects($this->any())->method('getData')->with('theme')->willReturn('Theme as a string');

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);

        $this->themeConfig->expects($this->never())->method('isThemeAssignedToStore');

        $this->themeObserver->cleanThemeRelatedContent($observerMock);
    }

    public function testThemeRegistration()
    {
        $pattern = 'some pattern';
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getPathPattern'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->any())->method('getPathPattern')->willReturn($pattern);
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $this->registration->expects($this->once())
            ->method('register')
            ->with($pattern)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('exception')));
        $this->logger->expects($this->once())
            ->method('critical');

        /** @var $observerMock \Magento\Framework\Event\Observer */
        $this->themeObserver->themeRegistration($observerMock);
    }
}
