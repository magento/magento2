<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Theme;

class ThemeProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetByFullPath()
    {
        $path = 'frontend/Magento/luma';
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $collectionMock = $this->getMock('Magento\Core\Model\Resource\Theme\Collection', [], [], '', false);
        $theme = $this->getMock('Magento\Framework\View\Design\ThemeInterface', [], [], '', false);
        $collectionMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            $path
        )->will(
            $this->returnValue($theme)
        );
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $themeFactory = $this->getMock('\Magento\Core\Model\ThemeFactory', [], [], '', false);

        $themeProvider = new ThemeProvider($collectionFactory, $themeFactory);
        $this->assertSame($theme, $themeProvider->getThemeByFullPath($path));
    }

    public function testGetById()
    {
        $themeId = 755;
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $theme = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $theme->expects($this->once())->method('load')->with($themeId)->will($this->returnSelf());
        $themeFactory = $this->getMock('\Magento\Core\Model\ThemeFactory', ['create'], [], '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($theme));

        $themeProvider = new ThemeProvider($collectionFactory, $themeFactory);
        $this->assertSame($theme, $themeProvider->getThemeById($themeId));
    }
}
