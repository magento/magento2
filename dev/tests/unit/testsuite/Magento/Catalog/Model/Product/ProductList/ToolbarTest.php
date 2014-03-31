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

namespace Magento\Catalog\Model\Product\ProductList;

class ToolbarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Toolbar
     */
    protected $toolbarModel;

    /**
     * @var \Magento\Stdlib\Cookie |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cookie;

    /**
     * @var \Magento\App\Request\Http |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->cookie = $this->getMock('Magento\Stdlib\Cookie', array('get'), array(), '', false);
        $this->request = $this->getMock('Magento\App\Request\Http', array('getParam'), array(), '', false);
        $this->toolbarModel = new Toolbar($this->cookie, $this->request);
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetOrder($param)
    {
        $this->cookie->expects($this->once())
            ->method('get')
            ->with(Toolbar::ORDER_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getOrder());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetDirection($param)
    {
        $this->cookie->expects($this->once())
            ->method('get')
            ->with(Toolbar::DIRECTION_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getDirection());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetMode($param)
    {
        $this->cookie->expects($this->once())
            ->method('get')
            ->with(Toolbar::MODE_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getMode());
    }

    /**
     * @dataProvider stringParamProvider
     * @param $param
     */
    public function testGetLimit($param)
    {
        $this->cookie->expects($this->once())
            ->method('get')
            ->with(Toolbar::LIMIT_COOKIE_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getLimit());
    }

    /**
     * @dataProvider intParamProvider
     * @param $param
     */
    public function testGetCurrentPage($param)
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->will($this->returnValue($param));
        $this->assertEquals($param, $this->toolbarModel->getCurrentPage());
    }

    public function testGetCurrentPageNoParam()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with(Toolbar::PAGE_PARM_NAME)
            ->will($this->returnValue(false));
        $this->assertEquals(1, $this->toolbarModel->getCurrentPage());
    }

    public function stringParamProvider()
    {
        return array(
            array('stringParam')
        );
    }

    public function intParamProvider()
    {
        return array(
            array('2'),
            array(3)
        );
    }
}

