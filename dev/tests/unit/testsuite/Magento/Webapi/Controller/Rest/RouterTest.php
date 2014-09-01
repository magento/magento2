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
namespace Magento\Webapi\Controller\Rest;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Router\Route */
    protected $_routeMock;

    /** @var \Magento\Webapi\Controller\Rest\Request */
    protected $_request;

    /** @var \Magento\Webapi\Model\Rest\Config */
    protected $_apiConfigMock;

    /** @var \Magento\Webapi\Controller\Rest\Router */
    protected $_router;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder(
            'Magento\Webapi\Model\Rest\Config'
        )->disableOriginalConstructor()->getMock();

        $this->_routeMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Router\Route'
        )->disableOriginalConstructor()->setMethods(
            array('match')
        )->getMock();

        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);

        $areaListMock->expects($this->once())
            ->method('getFrontName')
            ->will($this->returnValue('rest'));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_request = $objectManager->getObject(
            'Magento\Webapi\Controller\Rest\Request',
            [
                'areaList' => $areaListMock,
            ]
        );

        /** Initialize SUT. */
        $this->_router = $objectManager->getObject(
            'Magento\Webapi\Controller\Rest\Router',
            [
                'apiConfig' => $this->_apiConfigMock
            ]
        );
    }

    protected function tearDown()
    {
        unset($this->_routeMock);
        unset($this->_request);
        unset($this->_apiConfigMock);
        unset($this->_router);
        parent::tearDown();
    }

    public function testMatch()
    {
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->will(
            $this->returnValue(array($this->_routeMock))
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->will(
            $this->returnValue(array())
        );

        $matchedRoute = $this->_router->match($this->_request);
        $this->assertEquals($this->_routeMock, $matchedRoute);
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     */
    public function testNotMatch()
    {
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->will(
            $this->returnValue(array($this->_routeMock))
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->will(
            $this->returnValue(false)
        );

        $this->_router->match($this->_request);
    }
}
