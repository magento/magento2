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
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->responseMock = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $this->subjectMock = $this->getMock('Magento\App\FrontController', array(), array(), '', false);
        $this->plugin = new \Magento\App\FrontController\Plugin\Clickjacking();
    }

    public function testAfterDispatchIfHeaderExist()
    {
        $this->responseMock->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'X-Frame-Options'
        )->will(
            $this->returnValue(false)
        );
        $this->responseMock->expects(
            $this->once()
        )->method(
            'setHeader'
        )->with(
            'X-Frame-Options',
            'SAMEORIGIN'
        )->will(
            $this->returnValue($this->responseMock)
        );
        $this->assertEquals(
            $this->responseMock,
            $this->plugin->afterDispatch($this->subjectMock, $this->responseMock)
        );
    }

    public function testAfterDispatchIfHeaderNotExist()
    {
        $this->responseMock->expects(
            $this->once()
        )->method(
            'getHeader'
        )->with(
            'X-Frame-Options'
        )->will(
            $this->returnValue(true)
        );
        $this->responseMock->expects($this->never())->method('setHeader');
        $this->assertEquals(
            $this->responseMock,
            $this->plugin->afterDispatch($this->subjectMock, $this->responseMock)
        );
    }
}
