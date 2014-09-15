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
namespace Magento\Webapi\Controller\Soap\Request;

use Magento\Framework\Service\SimpleDataObjectConverter;
use Magento\Webapi\Model\Soap\Config as SoapConfig;

/**
 * Test for \Magento\Webapi\Controller\Soap\Request\Handler.
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Soap\Request\Handler */
    protected $_handler;

    /** @var \Magento\Framework\ObjectManager */
    protected $_objectManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_apiConfigMock;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_authorizationMock;

    /** @var SimpleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dataObjectConverter;

    /** @var \Magento\Webapi\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_serializerMock;

    /** @var array */
    protected $_arguments;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder('Magento\Webapi\Model\Soap\Config')
            ->setMethods(array('getServiceMethodInfo'))->disableOriginalConstructor()->getMock();
        $this->_requestMock = $this->getMock('Magento\Webapi\Controller\Soap\Request', [], [], '', false);
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $this->_authorizationMock = $this->getMock('Magento\Framework\AuthorizationInterface', [], [], '', false);
        $this->_dataObjectConverter = $this->getMock(
            'Magento\Framework\Service\SimpleDataObjectConverter',
            ['convertStdObjectToArray'],
            [],
            '',
            false
        );
        $this->_serializerMock = $this->getMock('Magento\Webapi\Controller\ServiceArgsSerializer', [], [], '', false);
        /** Initialize SUT. */
        $this->_handler = new \Magento\Webapi\Controller\Soap\Request\Handler(
            $this->_requestMock,
            $this->_objectManagerMock,
            $this->_apiConfigMock,
            $this->_authorizationMock,
            $this->_dataObjectConverter,
            $this->_serializerMock
        );
        parent::setUp();
    }

    public function testCall()
    {
        $requestedServices = array('requestedServices');
        $this->_requestMock->expects($this->once())
            ->method('getRequestedServices')
            ->will($this->returnValue($requestedServices));
        $this->_dataObjectConverter->expects($this->once())
            ->method('convertStdObjectToArray')
            ->will($this->returnValue(['field' => 1]));
        $operationName = 'soapOperation';
        $className = 'Magento\Framework\Object';
        $methodName = 'testMethod';
        $isSecure = false;
        $aclResources = array(array('Magento_TestModule::resourceA'));
        $this->_apiConfigMock->expects($this->once())
            ->method('getServiceMethodInfo')
            ->with($operationName, $requestedServices)
            ->will(
                $this->returnValue(
                    array(
                        SoapConfig::KEY_CLASS => $className,
                        SoapConfig::KEY_METHOD => $methodName,
                        SoapConfig::KEY_IS_SECURE => $isSecure,
                        SoapConfig::KEY_ACL_RESOURCES => $aclResources
                    )
                )
            );

        $this->_authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $serviceMock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods(array($methodName))
            ->getMock();

        $serviceResponse = array('foo' => 'bar');
        $serviceMock->expects($this->once())->method($methodName)->will($this->returnValue($serviceResponse));
        $this->_objectManagerMock->expects($this->once())->method('get')->with($className)
            ->will($this->returnValue($serviceMock));
        $this->_serializerMock->expects($this->once())->method('getInputData')->will($this->returnArgument(2));

        /** Execute SUT. */
        $this->assertEquals(
            array('result' => $serviceResponse),
            $this->_handler->__call($operationName, array((object)array('field' => 1)))
        );
    }
}
