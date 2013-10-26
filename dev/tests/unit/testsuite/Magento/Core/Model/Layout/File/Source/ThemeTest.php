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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Layout\File\Source;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\File\Source\Theme
     */
    private $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_dirs;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_fileFactory;

    protected function setUp()
    {
        $this->_filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $this->_dirs = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_dirs->expects($this->any())->method('getDir')->will($this->returnArgument(0));
        $this->_fileFactory = $this->getMock('Magento\Core\Model\Layout\File\Factory', array(), array(), '', false);
        $this->_model = new \Magento\Core\Model\Layout\File\Source\Theme(
            $this->_filesystem, $this->_dirs, $this->_fileFactory
        );
    }

    public function testGetFiles()
    {
        $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $theme->expects($this->once())->method('getFullPath')->will($this->returnValue('area/theme/path'));

        $this->_filesystem
            ->expects($this->once())
            ->method('searchKeys')
            ->with('design', 'area/theme/path/*_*/layout/*.xml')
            ->will($this->returnValue(array(
                'design/area/theme/path/Module_One/layout/1.xml',
                'design/area/theme/path/Module_One/layout/2.xml',
                'design/area/theme/path/Module_Two/layout/3.xml',
            )))
        ;

        $fileOne = new \Magento\Core\Model\Layout\File('1.xml', 'Module_One', $theme);
        $fileTwo = new \Magento\Core\Model\Layout\File('2.xml', 'Module_One', $theme);
        $fileThree = new \Magento\Core\Model\Layout\File('3.xml', 'Module_Two', $theme);
        $this->_fileFactory
            ->expects($this->at(0))
            ->method('create')
            ->with('design/area/theme/path/Module_One/layout/1.xml', 'Module_One', $theme)
            ->will($this->returnValue($fileOne))
        ;
        $this->_fileFactory
            ->expects($this->at(1))
            ->method('create')
            ->with('design/area/theme/path/Module_One/layout/2.xml', 'Module_One', $theme)
            ->will($this->returnValue($fileTwo))
        ;
        $this->_fileFactory
            ->expects($this->at(2))
            ->method('create')
            ->with('design/area/theme/path/Module_Two/layout/3.xml', 'Module_Two', $theme)
            ->will($this->returnValue($fileThree))
        ;

        $this->assertSame(array($fileOne, $fileTwo, $fileThree), $this->_model->getFiles($theme));
    }
}
