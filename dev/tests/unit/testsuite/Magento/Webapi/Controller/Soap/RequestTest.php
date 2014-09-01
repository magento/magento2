<?php
/**
 * SOAP API Request Test.
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
namespace Magento\Webapi\Controller\Soap;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_soapRequest;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $areaListMock = $this->getMock('Magento\Framework\App\AreaList', [], [], '', false);
        $areaListMock->expects($this->once())->method('getFrontName')->will($this->returnValue('soap'));

        /** Initialize SUT. */
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_soapRequest = $objectManager->getObject(
            'Magento\Webapi\Controller\Soap\Request',
            [
                'areaList' => $areaListMock
            ]
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapRequest);
        parent::tearDown();
    }

    public function testGetRequestedServicesNotAllowedParametersException()
    {
        /** Prepare mocks for SUT constructor. */
        $wsdlParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL;
        $servicesParam = \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES;
        // Set two not allowed parameters and all allowed
        $requestParams = array('param_1' => 'foo', 'param_2' => 'bar', $wsdlParam => true, $servicesParam => true);
        $this->_soapRequest->setParams($requestParams);
        $exceptionMessage = 'Not allowed parameters: param_1, param_2. Please use only wsdl and services.';
        /** Execute SUT. */
        try {
            $this->_soapRequest->getRequestedServices();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    public function testGetRequestedServicesEmptyRequestedServicesException()
    {
        /** Prepare mocks for SUT constructor. */
        $requestParams = array(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES => null);
        $this->_soapRequest->setParams($requestParams);
        $exceptionMessage = 'Incorrect format of WSDL request URI or Requested services are missing.';
        /** Execute SUT. */
        try {
            $this->_soapRequest->getRequestedServices();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    /**
     * @dataProvider providerTestGetRequestedServicesSuccess
     * @param $requestParamServices
     * @param $expectedResult
     */
    public function testGetRequestedServicesSuccess($requestParamServices, $expectedResult)
    {
        $requestParams = array(
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL => true,
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES => $requestParamServices
        );
        $this->_soapRequest->setParams($requestParams);
        $this->assertEquals($expectedResult, $this->_soapRequest->getRequestedServices());
    }

    public function providerTestGetRequestedServicesSuccess()
    {
        $testModuleA = 'testModule1AllSoapAndRestV1';
        $testModuleB = 'testModule1AllSoapAndRestV2';
        $testModuleC = 'testModule2AllSoapNoRestV1';
        return array(
            array("{$testModuleA},{$testModuleB}", array($testModuleA, $testModuleB)),
            array("{$testModuleA},{$testModuleC}", array($testModuleA, $testModuleC)),
            array("{$testModuleA}", array($testModuleA))
        );
    }
}
