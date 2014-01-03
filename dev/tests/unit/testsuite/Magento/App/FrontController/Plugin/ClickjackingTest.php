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

namespace Magento\App\FrontController\Plugin;

class ClickjackingTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\App\FrontController\Plugin\Clickjacking
     */
    protected $_plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;


    protected function setUp()
    {
        $this->_responseMock = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $this->_plugin = new \Magento\App\FrontController\Plugin\Clickjacking();
    }

    public function testAfterDispatchIfHeaderExist()
    {
        $this->_responseMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('X-Frame-Options')
            ->will($this->returnValue(false));
        $this->_responseMock
            ->expects($this->once())
            ->method('setHeader')
            ->with('X-Frame-Options', 'SAMEORIGIN')
            ->will($this->returnValue($this->_responseMock));
        $this->assertEquals($this->_responseMock, $this->_plugin->afterDispatch($this->_responseMock));
    }

    public function testAfterDispatchIfHeaderNotExist()
    {
        $this->_responseMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('X-Frame-Options')
            ->will($this->returnValue(true));
        $this->_responseMock
            ->expects($this->never())
            ->method('setHeader');
        $this->assertEquals($this->_responseMock, $this->_plugin->afterDispatch($this->_responseMock));
    }
}