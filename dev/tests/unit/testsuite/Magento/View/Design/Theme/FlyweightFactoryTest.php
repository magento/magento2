<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *   
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View\Design\Theme;

class FlyweightFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\View\Design\Theme\ThemeProviderInterface
     */
    protected $themeProviderMock;

    /**
     * @var FlyweightFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->themeProviderMock = $this->getMock('Magento\View\Design\Theme\ThemeProviderInterface');
        $this->factory = new FlyweightFactory($this->themeProviderMock);
    }

    /**
     * @covers \Magento\View\Design\Theme\FlyweightFactory::create
     */
    public function testCreateById()
    {
        $themeId = 5;
        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $theme->expects($this->exactly(3))->method('getId')->will($this->returnValue($themeId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue(null));

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeById'
        )->with(
            $themeId
        )->will(
            $this->returnValue($theme)
        );

        $this->assertSame($theme, $this->factory->create($themeId));
    }

    /**
     * @covers \Magento\View\Design\Theme\FlyweightFactory::create
     */
    public function testCreateByPath()
    {
        $path = 'frontend/magento_plushe';
        $themeId = 7;
        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $theme->expects($this->exactly(3))->method('getId')->will($this->returnValue($themeId));

        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue($path));

        $this->themeProviderMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            'frontend/frontend/magento_plushe'
        )->will(
            $this->returnValue($theme)
        );

        $this->assertSame($theme, $this->factory->create($path));
    }

    public function testCreateDummy()
    {
        $themeId = 0;
        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
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
