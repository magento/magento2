<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollectionResourceModel;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Theme\Plugin\Registration as RegistrationPlugin;
use Magento\Theme\Model\Theme\Registration as ThemeRegistration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RegistrationTest extends TestCase
{
    /**
     * @var ThemeRegistration|MockObject
     */
    protected $themeRegistrationMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var ActionInterface|MockObject
     */
    protected $actionMock;

    /**
     * @var State|MockObject
     */
    protected $appStateMock;

    /**
     * @var ThemeCollection|MockObject
     */
    protected $themeCollectionMock;

    /**
     * @var ThemeCollectionResourceModel|MockObject
     */
    protected $themeLoaderMock;

    /**
     * @var RegistrationPlugin
     */
    protected $plugin;

    protected function setUp(): void
    {
        $this->themeRegistrationMock = $this->createMock(ThemeRegistration::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->themeCollectionMock = $this->createMock(ThemeCollection::class);
        $this->themeLoaderMock = $this->createMock(ThemeCollectionResourceModel::class);

        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(RegistrationPlugin::class, [
            'themeRegistration' => $this->themeRegistrationMock,
            'themeCollection' => $this->themeCollectionMock,
            'themeLoader' => $this->themeLoaderMock,
            'logger' => $this->loggerMock,
            'appState' => $this->appStateMock
        ]);
    }

    /**
     * @param bool $hasParentTheme
     * @dataProvider dataProviderBeforeExecute
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testBeforeExecute($hasParentTheme)
    {
        $themeId = 1;
        $themeTitle = 'Theme title';

        $themeFromConfigMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->addMethods(['getThemeTitle'])
            ->onlyMethods([
                'getArea',
                'getThemePath',
                'getParentTheme',
            ])
            ->getMock();

        $themeFromDbMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'save',
            ])
            ->addMethods(['setParentId', 'setThemeTitle'])
            ->getMock();

        $parentThemeFromDbMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parentThemeFromConfigMock = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn('default');

        $this->themeRegistrationMock->expects($this->once())
            ->method('register');

        $this->themeCollectionMock->expects($this->once())
            ->method('loadData')
            ->willReturn([$themeFromConfigMock]);

        $this->themeLoaderMock->expects($hasParentTheme ? $this->exactly(2) : $this->once())
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

        $this->plugin->beforeExecute($this->actionMock);
    }

    /**
     * @return array
     */
    public static function dataProviderBeforeExecute()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testBeforeDispatchWithProductionMode()
    {
        $this->appStateMock->expects($this->once())->method('getMode')->willReturn('production');
        $this->plugin->beforeExecute($this->actionMock);
    }

    public function testBeforeDispatchWithException()
    {
        $exception = new LocalizedException(new Phrase('Phrase'));
        $this->themeRegistrationMock->expects($this->once())->method('register')->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical');

        $this->plugin->beforeExecute($this->actionMock);
    }
}
