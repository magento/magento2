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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Controller\Varien;

/**
 * Test class \Magento\Core\Controller\Varien\AbstractAction
 */
class AbstractActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Action\AbstractAction
     */
    protected $_actionAbstract;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * Setup before tests
     *
     * Create request, response and forward action (child of AbstractAction)
     */
    protected function setUp()
    {
        $this->_request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $this->_response->headersSentThrowsException = false;
        $this->_actionAbstract = new \Magento\App\Action\Forward($this->_request, $this->_response);
    }

    /**
     * Test for getRequest method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getRequest
     */
    public function testGetRequest()
    {
        $this->assertEquals($this->_request, $this->_actionAbstract->getRequest());
    }

    /**
     * Test for getResponse method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getResponse
     */
    public function testGetResponse()
    {
        $this->assertEquals($this->_response, $this->_actionAbstract->getResponse());
    }

    /**
     * Test for getResponse med. Checks that response headers are set correctly
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getResponse
     */
    public function testResponseHeaders()
    {
        $routerListMock = $this->getMock('\Magento\App\RouterList', array(), array(), '', false);
        $request = new \Magento\App\Request\Http($routerListMock);
        $response = new \Magento\App\Response\Http();
        $response->headersSentThrowsException = false;
        $action = new \Magento\App\Action\Forward($request, $response);

        $headers = array(
            array(
                'name' => 'X-Frame-Options',
                'value' => 'SAMEORIGIN',
                'replace' => false
            )
        );

        $this->assertEquals($headers, $action->getResponse()->getHeaders());
    }

    /**
     * Test for getFullActionName method
     *
     * @test
     * @covers \Magento\Core\Controller\Varien\AbstractAction::getFullActionName
     */
    public function testGetFullActionName()
    {
        $this->_request->expects($this->once())
            ->method('getRequestedRouteName')
            ->will($this->returnValue('adminhtml'));

        $this->_request->expects($this->once())
            ->method('getRequestedControllerName')
            ->will($this->returnValue('index'));

        $this->_request->expects($this->once())
            ->method('getRequestedActionName')
            ->will($this->returnValue('index'));

        $this->assertEquals('adminhtml_index_index', $this->_actionAbstract->getFullActionName());
    }
}
