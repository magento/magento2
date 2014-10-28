<?php
/**
 * Test Rest renderer factory class.
 *
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
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Response\Renderer\Factory */
    protected $_factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_requestMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request'
        )->disableOriginalConstructor()->getMock();

        $renders = array(
            'default' => array('type' => '*/*', 'model' => 'Magento\Webapi\Controller\Rest\Response\Renderer\Json'),
            'application_json' => array(
                'type' => 'application/json',
                'model' => 'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
            )
        );

        $this->_factory = new \Magento\Webapi\Controller\Rest\Response\Renderer\Factory(
            $this->_objectManagerMock,
            $this->_requestMock,
            $renders
        );
    }

    /**
     * Test GET method.
     */
    public function testGet()
    {
        $acceptTypes = array('application/json');

        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue($acceptTypes));
        /** Mock renderer. */
        $rendererMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
        )->disableOriginalConstructor()->getMock();
        /** Mock object to return mocked renderer. */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
        )->will(
            $this->returnValue($rendererMock)
        );
        $this->_factory->get();
    }

    /**
     * Test GET method with wrong Accept HTTP Header.
     */
    public function testGetWithWrongAcceptHttpHeader()
    {
        /** Mock request to return empty Accept Types. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue(''));
        try {
            $this->_factory->get();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $exceptionMessage = 'Server cannot understand Accept HTTP header media type.';
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    /**
     * Test GET method with wrong Renderer class.
     */
    public function testGetWithWrongRendererClass()
    {
        $acceptTypes = array('application/json');
        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue($acceptTypes));
        /** Mock object to return \Magento\Framework\Object */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
        )->will(
            $this->returnValue(new \Magento\Framework\Object())
        );

        $this->setExpectedException(
            'LogicException',
            'The renderer must implement "Magento\Webapi\Controller\Rest\Response\RendererInterface".'
        );
        $this->_factory->get();
    }
}
