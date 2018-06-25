<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Webapi\Request */
    protected $request;

    protected function setUp()
    {
        /** Initialize SUT. */
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->request = $objectManager->getObject('Magento\Framework\Webapi\Request');
    }

    protected function tearDown()
    {
        unset($this->request);
        parent::tearDown();
    }

    /**
     * @dataProvider providerTestGetRequestedServicesSuccess
     * @param $requestParamServices
     * @param $expectedResult
     */
    public function testGetRequestedServicesSuccess($requestParamServices, $expectedResult)
    {
        $requestParams = [
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_WSDL => true,
            \Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_SERVICES => $requestParamServices,
        ];
        $this->request->setParams($requestParams);
        $this->assertEquals($expectedResult, $this->request->getRequestedServices());
    }

    /**
     * @return array
     */
    public function providerTestGetRequestedServicesSuccess()
    {
        $testModuleA = 'testModule1AllSoapAndRestV1';
        $testModuleB = 'testModule1AllSoapAndRestV2';
        $testModuleC = 'testModule2AllSoapNoRestV1';
        return [
            ["{$testModuleA},{$testModuleB}", [$testModuleA, $testModuleB]],
            ["{$testModuleA},{$testModuleC}", [$testModuleA, $testModuleC]],
            ["{$testModuleA}", [$testModuleA]]
        ];
    }
}
