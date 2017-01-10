<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Plugin;

use Magento\Theme\Model\Theme\Plugin\Registration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class RegistrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\Theme\Registration|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeRegistration;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Backend\App\AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $abstractAction;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var \Magento\Theme\Model\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeCollection;

    /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeLoader;

    /** @var Registration */
    protected $plugin;

    protected function setUp()
    {
        $this->themeRegistration = $this->getMock(\Magento\Theme\Model\Theme\Registration::class, [], [], '', false);
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->abstractAction = $this->getMockForAbstractClass(
            \Magento\Backend\App\AbstractAction::class,
            [],
            '',
            false
        );
        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $this->appState = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->themeCollection = $this->getMock(\Magento\Theme\Model\Theme\Collection::class, [], [], '', false);
        $this->themeLoader = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->plugin = new Registration(
            $this->themeRegistration,
            $this->themeCollection,
            $this->themeLoader,
            $this->logger,
            $this->appState
        );
    }

    /**
     * @param bool $hasParentTheme
     * @dataProvider dataProviderBeforeDispatch
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testBeforeDispatch(
        $hasParentTheme
    ) {
        $themeId = 1;
        $themeTitle = 'Theme title';

        $themeFromConfigMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getArea',
                'getThemePath',
                'getParentTheme',
                'getThemeTitle',
            ])
            ->getMock();

        $themeFromDbMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setParentId',
                'setThemeTitle',
                'save',
            ])
            ->getMock();

        $parentThemeFromDbMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parentThemeFromConfigMock = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn('default');

        $this->themeRegistration->expects($this->once())
            ->method('register');

        $this->themeCollection->expects($this->once())
            ->method('loadData')
            ->willReturn([$themeFromConfigMock]);

        $this->themeLoader->expects($hasParentTheme ? $this->exactly(2) : $this->once())
            ->method('getThemeByFullPath')
            ->willReturnMap([
                ['frontend/Magento/blank', $parentThemeFromDbMock],
                ['frontend/Magento/luma', $themeFromDbMock],
            ]);

        $themeFromConfigMock->expects($this->once())
            ->method('getArea')
            ->willReturn('frontend');
        $themeFromConfigMock->expects($this->once())
            ->method('getThemePath')
            ->willReturn('Magento/luma');
        $themeFromConfigMock->expects($hasParentTheme ? $this->exactly(2) : $this->once())
            ->method('getParentTheme')
            ->willReturn($hasParentTheme ? $parentThemeFromConfigMock : null);
        $themeFromConfigMock->expects($this->once())
            ->method('getThemeTitle')
            ->willReturn($themeTitle);

        $parentThemeFromDbMock->expects($hasParentTheme ? $this->once() : $this->never())
            ->method('getId')
            ->willReturn($themeId);

        $parentThemeFromConfigMock->expects($hasParentTheme ? $this->once() : $this->never())
            ->method('getFullPath')
            ->willReturn('frontend/Magento/blank');

        $themeFromDbMock->expects($hasParentTheme ? $this->once() : $this->never())
            ->method('setParentId')
            ->with($themeId)
            ->willReturnSelf();
        $themeFromDbMock->expects($this->once())
            ->method('setThemeTitle')
            ->with($themeTitle)
            ->willReturnSelf();
        $themeFromDbMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }

    /**
     * @return array
     */
    public function dataProviderBeforeDispatch()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testBeforeDispatchWithProductionMode()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn('production');
        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }

    public function testBeforeDispatchWithException()
    {
        $exception = new LocalizedException(new Phrase('Phrase'));
        $this->themeRegistration->expects($this->once())->method('register')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical');

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
    }
}
