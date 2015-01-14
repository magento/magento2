<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

class FlyweightFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProviderMock;

    /**
     * @var FlyweightFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->themeProviderMock = $this->getMock('Magento\Framework\View\Design\Theme\ThemeProviderInterface');
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
        $theme = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $theme->expects($this->exactly(3))->method('getId')->will($this->returnValue($expectedId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue(null));

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
        $theme = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $theme->expects($this->exactly(3))->method('getId')->will($this->returnValue($themeId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue($path));

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

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to load theme by specified key: '0'
     */
    public function testCreateDummy()
    {
        $themeId = 0;
        $theme = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $theme->expects($this->once())->method('getId')->will($this->returnValue($themeId));

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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect theme identification key
     */
    public function testNegativeCreate()
    {
        $this->factory->create(null);
    }
}
