<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Theme\Model\Theme\Plugin\Registration;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class RegistrationTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Theme\Model\Theme\Registration|\PHPUnit_Framework_MockObject_MockObject */
    protected $themeRegistration;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $action;

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
        $this->themeRegistration = $this->createMock(\Magento\Theme\Model\Theme\Registration::class);
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class, [], '', false);
        $this->action = $this->createMock(ActionInterface::class);
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);
        $this->themeCollection = $this->createMock(\Magento\Theme\Model\Theme\Collection::class);
        $this->themeLoader = $this->createMock(\Magento\Theme\Model\ResourceModel\Theme\Collection::class);
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
     * @dataProvider dataProviderBeforeExecute
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testBeforeExecute(
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

        $this->plugin->beforeExecute($this->action);
    }

    /**
     * @return array
     */
    public function dataProviderBeforeExecute()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testBeforeDispatchWithProductionMode()
    {
        $this->appState->expects($this->once())->method('getMode')->willReturn('production');
        $this->plugin->beforeExecute($this->action);
    }

    public function testBeforeDispatchWithException()
    {
        $exception = new LocalizedException(new Phrase('Phrase'));
        $this->themeRegistration->expects($this->once())->method('register')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical');

        $this->plugin->beforeExecute($this->action);
    }
}
