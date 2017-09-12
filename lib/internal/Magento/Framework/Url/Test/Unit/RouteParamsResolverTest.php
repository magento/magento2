<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\ParamEncoder;
use Magento\Framework\Url\QueryParamsResolverInterface;
use Magento\Framework\Url\RouteParamsResolver;
use Zend\Stdlib\ParametersInterface;

/**
 * Test for \Magento\Framework\Url\RouteParamsResolver.
 */
class RouteParamsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteParamsResolver
     */
    private $routeParamsResolver;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var QueryParamsResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryParamsResolverMock;

    /**
     * @var ParamEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paramEncoderMock;

    /**
     * @var ParametersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryParamsMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParamsResolverMock = $this->getMockBuilder(QueryParamsResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paramEncoderMock = $this->getMockBuilder(ParamEncoder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryParamsMock = $this->getMockBuilder(ParametersInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider setRouteParamsDataProvider
     */
    public function testSetRouteParamsEscapeParams($dataForParamsResolver)
    {
        $paramKey = '_direct';
        $paramValue = 'http://param';
        $encodedParamKey = '_direct';
        $encodedParamValue = 'http%3A%2F%2Fparam';
        $data = [
            '_direct' => $paramValue,
        ];

        $this->routeParamsResolver = new RouteParamsResolver(
            $this->requestMock,
            $this->queryParamsResolverMock,
            $dataForParamsResolver,
            $this->paramEncoderMock
        );

        if (isset($dataForParamsResolver['escape_params'])) {
            $this->paramEncoderMock
                ->expects($this->exactly(2))
                ->method('encode')
                ->withConsecutive([$paramKey], [$paramValue])
                ->willReturnOnConsecutiveCalls($encodedParamKey, $encodedParamValue);
            $paramKey = $encodedParamKey;
            $paramValue = $encodedParamValue;
        }

        $resultRouteParams = [$paramKey => $paramValue];
        $this->routeParamsResolver->setRouteParams($data);

        $this->assertEquals($resultRouteParams, $this->routeParamsResolver->getRouteParams());
    }

    public function setRouteParamsDataProvider()
    {
        return [
            'escape_params' => [
                ['escape_params' => true],

            ],
            'no_escape_params' => [
                [],
            ]
        ];
    }
}
