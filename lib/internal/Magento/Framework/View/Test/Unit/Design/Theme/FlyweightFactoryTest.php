<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Theme;

use \Magento\Framework\View\Design\Theme\FlyweightFactory;

class FlyweightFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProviderMock;

    /**
     * @var FlyweightFactory
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->themeProviderMock =
            $this->createMock(\Magento\Framework\View\Design\Theme\ThemeProviderInterface::class);
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
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $theme->expects($this->exactly(2))->method('getId')->willReturn($expectedId);

        $theme->expects($this->once())->method('getFullPath')->willReturn(null);
        $theme->expects($this->once())->method('getCode')->willReturn($expectedId);
        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeById'
        )->with(
            $expectedId
        )->willReturn(
            $theme
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
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $theme->expects($this->exactly(2))->method('getId')->willReturn($themeId);

        $theme->expects($this->once())->method('getFullPath')->willReturn($path);
        $theme->expects($this->once())->method('getCode')->willReturn('Magento/luma');
        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'frontend/frontend/Magento/luma'
        )->willReturn(
            $theme
        );

        $this->assertSame($theme, $this->factory->create($path));
    }

    /**
     */
    public function testCreateDummy()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unable to load theme by specified key: \'0\'');

        $themeId = 0;
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeById'
        )->with(
            $themeId
        )->willReturn(
            $theme
        );

        $this->assertNull($this->factory->create($themeId));
    }

    /**
     */
    public function testNegativeCreate()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect theme identification key');

        $this->factory->create(null);
    }
}
