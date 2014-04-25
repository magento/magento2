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
namespace Magento\Core\Model\Theme;

class ThemeProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetByFullPath()
    {
        $path = 'frontend/Magento/plushe';
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $collectionMock = $this->getMock('Magento\Core\Model\Resource\Theme\Collection', array(), array(), '', false);
        $theme = $this->getMock('Magento\Framework\View\Design\ThemeInterface', array(), array(), '', false);
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
        $themeFactory = $this->getMock('\Magento\Core\Model\ThemeFactory', array(), array(), '', false);

        $themeProvider = new ThemeProvider($collectionFactory, $themeFactory);
        $this->assertSame($theme, $themeProvider->getThemeByFullPath($path));
    }

    public function testGetById()
    {
        $themeId = 755;
        $collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Theme\CollectionFactory',
            array(),
            array(),
            '',
            false
        );
        $theme = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $theme->expects($this->once())->method('load')->with($themeId)->will($this->returnSelf());
        $themeFactory = $this->getMock('\Magento\Core\Model\ThemeFactory', array('create'), array(), '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($theme));

        $themeProvider = new ThemeProvider($collectionFactory, $themeFactory);
        $this->assertSame($theme, $themeProvider->getThemeById($themeId));
    }
}
