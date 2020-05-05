<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\View;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\View\Design;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DesignTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    protected $state;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var FlyweightFactory|MockObject
     */
    protected $flyweightThemeFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory|MockObject
     */
    protected $themeFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $config;

    /**
     * @var string|MockObject
     */
    protected $defaultTheme = 'anyName4Theme';

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var Design::__construct
     */
    private $model;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->flyweightThemeFactory = $this->createMock(FlyweightFactory::class);
        $this->config = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->state = $this->createMock(State::class);
        $themes = [Design::DEFAULT_AREA => $this->defaultTheme];
        $this->model = new Design(
            $this->storeManager,
            $this->flyweightThemeFactory,
            $this->config,
            $this->themeFactory,
            $this->objectManager,
            $this->state,
            $themes
        );
    }

    /**
     * @param string $themePath
     * @param string $themeId
     * @param string $expectedResult
     * @dataProvider getThemePathDataProvider
     */
    public function testGetThemePath($themePath, $themeId, $expectedResult)
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $theme->expects($this->once())->method('getThemePath')->willReturn($themePath);
        $theme->expects($this->any())->method('getId')->willReturn($themeId);
        /** @var ThemeInterface $theme */
        $this->assertEquals($expectedResult, $this->model->getThemePath($theme));
    }

    /**
     * @return array
     */
    public function getThemePathDataProvider()
    {
        return [
            ['some_path', '', 'some_path'],
            ['', '2', DesignInterface::PUBLIC_THEME_DIR . '2'],
            ['', '', DesignInterface::PUBLIC_VIEW_DIR],
        ];
    }

    /**
     * @return array
     */
    public function designThemeDataProvider()
    {
        return [
            'single' => [true, ScopeInterface::SCOPE_WEBSITES],
            'multi'  => [false, ScopeInterface::SCOPE_STORE],
        ];
    }

    /**
     * @test
     * @param bool $storeMode
     * @param string $scope
     * @dataProvider designThemeDataProvider
     * @return void
     */
    public function testSetDefaultDesignTheme($storeMode, $scope)
    {
        $area = Design::DEFAULT_AREA;
        $this->state->expects($this->any())
            ->method('getAreaCode')
            ->willReturn($area);
        $this->storeManager->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn($storeMode);
        $this->config->expects($this->once())
            ->method('getValue')
            ->with(Design::XML_PATH_THEME_ID, $scope, null)
            ->willReturn(null);
        $this->flyweightThemeFactory->expects($this->once())
            ->method('create')
            ->with($this->defaultTheme, $area);
        $this->assertInstanceOf(get_class($this->model), $this->model->setDefaultDesignTheme());
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\View\Design::getDesignParams
     * @covers \Magento\Theme\Model\View\Design::getLocale
     * @covers \Magento\Theme\Model\View\Design::getArea
     * @covers \Magento\Theme\Model\View\Design::getDesignTheme
     */
    public function testGetDesignParams()
    {
        $locale = 'locale';
        $area = Design::DEFAULT_AREA;
        $localeMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $localeMock->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->willReturn($localeMock);
        $this->state->expects($this->any())
            ->method('getAreaCode')
            ->willReturn($area);
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockBuilder(ThemeInterface::class)
            ->getMock());

        $params = $this->model->getDesignParams();

        $this->assertInstanceOf(ThemeInterface::class, $params['themeModel']);
        $this->assertEquals($area, $params['area']);
        $this->assertEquals($locale, $params['locale']);
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\View\Design::setDesignTheme
     * @covers \Magento\Theme\Model\View\Design::setArea
     */
    public function testSetDesignTheme()
    {
        $area = 'adminhtml';
        $theme = $this->getMockBuilder(ThemeInterface::class)
            ->getMock();

        $this->assertInstanceOf(get_class($this->model), $this->model->setDesignTheme($theme, $area));
    }
}
