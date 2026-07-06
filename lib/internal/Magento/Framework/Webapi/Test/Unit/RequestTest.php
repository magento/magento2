<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Request;
use Magento\Webapi\Model\Soap\Server;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RequestTest extends TestCase
{
    /** @var Request */
    protected $request;

    protected function setUp(): void
    {
        /** Initialize SUT. */
        $objectManager = new ObjectManager($this);
        $this->request = $objectManager->getObject(Request::class);
    }

    protected function tearDown(): void
    {
        unset($this->request);
        parent::tearDown();
    }

    /**     * @param $requestParamServices
     * @param $expectedResult
     */
    #[DataProvider('providerTestGetRequestedServicesSuccess')]
    public function testGetRequestedServicesSuccess($requestParamServices, $expectedResult)
    {
        $requestParams = [
            Server::REQUEST_PARAM_WSDL => true,
            Server::REQUEST_PARAM_SERVICES => $requestParamServices,
        ];
        $this->request->setParams($requestParams);
        $this->assertEquals($expectedResult, $this->request->getRequestedServices());
    }

    /**
     * @return array
     */
    public static function providerTestGetRequestedServicesSuccess()
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
