<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Theme\Test;

use Magento\Framework\Theme\Dir;

/**
 * Tests Dir
 */
class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Component registry
     *
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrarMock;

    /**
     * Tests getAreaConfiguration()
     *
     */
    public function testGetAreaConfiguration()
    {
        $this->componentRegistrarMock = $this->getMockBuilder('Magento\Framework\Component\ComponentRegistrarInterface')
            ->disableOriginalConstructor()->getMock();
        $this->componentRegistrarMock->expects($this->once())
            ->method('getPaths')
            ->will($this->returnValue(['adminhtml/Magento/sampleTheme' => 'path/to/the/sample/theme']));
        $expected = ['area' => 'adminhtml', 'theme_path_pieces' => ['Magento', 'sampleTheme']];
        $dir = new Dir($this->componentRegistrarMock);
        $this->assertEquals($expected, $dir->getAreaConfiguration("sample/theme"));

    }
}
