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
namespace Magento\Framework\Module;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stringMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    protected function setUp()
    {
        $this->filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false, false);
        $this->directoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_stringMock = $this->getMock('Magento\Framework\Stdlib\String', array(), array(), '', false, false);

        $this->_stringMock->expects($this->once())->method('upperCaseWords')->will($this->returnValue('Test/Module'));

        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($this->directoryMock)
        );

        $this->_model = new \Magento\Framework\Module\Dir($this->filesystemMock, $this->_stringMock);
    }

    public function testGetDirModuleRoot()
    {
        $this->directoryMock->expects(
            $this->once()
        )->method(
            'getAbsolutePath'
        )->with(
            'Test/Module'
        )->will(
            $this->returnValue('/Test/Module')
        );
        $this->assertEquals('/Test/Module', $this->_model->getDir('Test_Module'));
    }

    public function testGetDirModuleSubDir()
    {
        $this->directoryMock->expects(
            $this->once()
        )->method(
            'getAbsolutePath'
        )->with(
            'Test/Module/etc'
        )->will(
            $this->returnValue('/Test/Module/etc')
        );
        $this->assertEquals('/Test/Module/etc', $this->_model->getDir('Test_Module', 'etc'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Directory type 'unknown' is not recognized
     */
    public function testGetDirModuleSubDirUnknown()
    {
        $this->_model->getDir('Test_Module', 'unknown');
    }
}
