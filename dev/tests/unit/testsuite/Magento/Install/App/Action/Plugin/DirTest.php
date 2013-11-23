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
namespace Magento\Install\App\Action\Plugin;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Install\App\Action\Plugin\Dir
     */
    protected $_plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirMock;

    protected function setUp()
    {
        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->_dirMock = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_plugin = new \Magento\Install\App\Action\Plugin\Dir(
            $this->_appStateMock,
            $this->_dirMock
        );
    }

    public function testBeforeDispatchWhenAppIsInstalled()
    {
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(false));
        $this->_dirMock
            ->expects($this->once())
            ->method('getDir')
            ->with(\Magento\App\Dir::VAR_DIR)->will($this->returnValue('dir_name'));
        $this->assertEquals(array(), $this->_plugin->beforeDispatch(array()));
    }

    public function testBeforeDispatchWhenAppIsNotInstalled()
    {
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));
        $this->_dirMock->expects($this->never())->method('getDir');
        $this->assertEquals(array(), $this->_plugin->beforeDispatch(array()));
    }
}