<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Theme\Test\Unit\Model\View;

use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\View\Design;

class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flyweightThemeFactory;

    /**
     * @var \Magento\Theme\Model\ThemeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var string|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultTheme = 'anyName4Theme';

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var Design::__construct
     */
    private $model;

    protected function setUp()
    {
        $this->storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $this->flyweightThemeFactory = $this->getMock(
            'Magento\Framework\View\Design\Theme\FlyweightFactory', [], [], '', false
        );
        $this->config = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->themeFactory = $this->getMock('Magento\Theme\Model\ThemeFactory', ['create'], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $this->state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
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
        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getThemePath')->will($this->returnValue($themePath));
        $theme->expects($this->any())->method('getId')->will($this->returnValue($themeId));
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $this->assertEquals($expectedResult, $this->model->getThemePath($theme));
    }

    /**
     * @return array
     */
    public function getThemePathDataProvider()
    {
        return [
            ['some_path', '', 'some_path'],
            ['', '2', \Magento\Framework\View\DesignInterface::PUBLIC_THEME_DIR . '2'],
            ['', '', \Magento\Framework\View\DesignInterface::PUBLIC_VIEW_DIR],
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
        $localeMock = $this->getMockForAbstractClass('\Magento\Framework\Locale\ResolverInterface');
        $localeMock->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue($locale));
        $this->objectManager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($localeMock));
        $this->state->expects($this->any())
            ->method('getAreaCode')
            ->willReturn($area);
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMock());

        $params = $this->model->getDesignParams();

        $this->assertInstanceOf('Magento\Framework\View\Design\ThemeInterface', $params['themeModel']);
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
        $theme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')->getMock();

        $this->assertInstanceOf(get_class($this->model), $this->model->setDesignTheme($theme, $area));
    }
}
