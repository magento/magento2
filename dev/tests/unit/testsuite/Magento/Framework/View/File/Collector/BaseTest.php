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

namespace Magento\Framework\View\File\Collector;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFiles()
    {
        $directory = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', ['getDirectoryRead'], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\Framework\App\Filesystem::MODULES_DIR)
            ->will($this->returnValue($directory));
        $globalFiles = [
            'Namespace/One/view/base/layout/one.xml',
            'Namespace/Two/view/base/layout/two.xml',
        ];
        $areaFiles = [
            'Namespace/Two/view/frontend/layout/four.txt',
            'Namespace/Two/view/frontend/layout/three.xml',
        ];
        $directory->expects($this->at(0))
            ->method('search')
            ->with('*/*/view/base/layout/*.xml')
            ->will($this->returnValue($globalFiles));
        $directory->expects($this->at(3))
            ->method('search')
            ->with('*/*/view/frontend/layout/*.xml')
            ->will($this->returnValue($areaFiles));
        $directory->expects($this->atLeastOnce())->method('getAbsolutePath')->will($this->returnArgument(0));
        $objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManager');
        $objectManager->expects($this->atLeastOnce())
            ->method('create')
            ->with('Magento\Framework\View\File', $this->anything())
            ->will($this->returnCallback(array($this, 'createFileCallback')));
        $fileFactory = new \Magento\Framework\View\File\Factory($objectManager);
        $theme = $this->getMock(
            'Magento\Framework\View\Design\ThemeInterface',
            [
                'getArea',
                'getThemePath',
                'getFullPath',
                'getParentTheme',
                'getCode',
                'isPhysical',
                'getInheritedThemes',
                'getId',
                'getData'
            ]
        );
        $theme->expects($this->once())->method('getData')->with('area')->will($this->returnValue('frontend'));
        $model = new Base($filesystem, $fileFactory, 'layout');
        $result = $model->getFiles($theme, '*.xml');

        for ($i = 0; $i <= 2; $i++) {
            $this->assertArrayHasKey($i, $result);
            $this->assertInstanceOf('\Magento\Framework\View\File', $result[$i]);
        }
        $this->assertEquals($globalFiles[0], $result[0]->getFilename());
        $this->assertEquals($globalFiles[1], $result[1]->getFilename());
        $this->assertEquals($areaFiles[1], $result[2]->getFilename());
    }

    /**
     * A callback subroutine for testing creation of value objects
     *
     * @param string $class
     * @param array $args
     * @return object
     */
    public function createFileCallback($class, $args)
    {
        return new $class($args['filename'], $args['module'], $args['theme'], $args['isBase']);
    }
}
