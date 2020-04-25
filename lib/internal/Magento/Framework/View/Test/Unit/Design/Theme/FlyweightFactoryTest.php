<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Theme\Model\Theme;
use \Magento\Framework\View\Design\Theme\FlyweightFactory;

class FlyweightFactoryTest extends TestCase
{
    /**
     * @var MockObject|ThemeProviderInterface
     */
    protected $themeProviderMock;

    /**
     * @var FlyweightFactory
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->themeProviderMock =
            $this->createMock(ThemeProviderInterface::class);
        $this->factory = new FlyweightFactory($this->themeProviderMock);
    }

    /**
     * @param string $path
     * @param int $expectedId
     * @dataProvider createByIdDataProvider
     * @covers \Magento\Framework\View\Design\Theme\FlyweightFactory::create
     */
    public function testCreateById($path, $expectedId)
    {
        $theme = $this->createMock(Theme::class);
        $theme->expects($this->exactly(2))->method('getId')->will($this->returnValue($expectedId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue(null));
        $theme->expects($this->once())->method('getCode')->willReturn($expectedId);
        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeById'
        )->with(
            $expectedId
        )->will(
            $this->returnValue($theme)
        );

        $this->assertSame($theme, $this->factory->create($path));
    }

    /**
     * @return array
     */
    public function createByIdDataProvider()
    {
        return [
            [5, 5],
            ['_theme10', 10],
        ];
    }

    /**
     * @covers \Magento\Framework\View\Design\Theme\FlyweightFactory::create
     */
    public function testCreateByPath()
    {
        $path = 'frontend/Magento/luma';
        $themeId = 7;
        $theme = $this->createMock(Theme::class);
        $theme->expects($this->exactly(2))->method('getId')->will($this->returnValue($themeId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue($path));
        $theme->expects($this->once())->method('getCode')->willReturn('Magento/luma');
        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'frontend/frontend/Magento/luma'
        )->will(
            $this->returnValue($theme)
        );

        $this->assertSame($theme, $this->factory->create($path));
    }

    public function testCreateDummy()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unable to load theme by specified key: \'0\'');
        $themeId = 0;
        $theme = $this->createMock(Theme::class);

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeById'
        )->with(
            $themeId
        )->will(
            $this->returnValue($theme)
        );

        $this->assertNull($this->factory->create($themeId));
    }

    public function testNegativeCreate()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Incorrect theme identification key');
        $this->factory->create(null);
    }
}
