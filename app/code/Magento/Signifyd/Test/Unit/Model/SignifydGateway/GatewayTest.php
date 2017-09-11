<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway;

use \PHPUnit\Framework\TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilderInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;

class GatewayTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreateCaseBuilderInterface|MockObject
     */
    private $createCaseBuilder;

    /**
     * @var ApiClient|MockObject
     */
    private $apiClient;

    /**
     * @var Gateway
     */
    private $gateway;

    public function setUp()
    {
        $this->createCaseBuilder = $this->getMockBuilder(CreateCaseBuilderInterface::class)
            ->getMockForAbstractClass();

        $this->apiClient = $this->getMockBuilder(ApiClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gateway = new Gateway(
            $this->createCaseBuilder,
            $this->apiClient
        );
    }

    public function testCreateCaseForSpecifiedOrder()
    {
        $dummyOrderId = 1;
        $dummySignifydInvestigationId = 42;
        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'investigationId' => $dummySignifydInvestigationId
            ]);

        $this->createCaseBuilder
            ->expects($this->atLeastOnce())
            ->method('build')
            ->with($this->equalTo($dummyOrderId))
            ->willReturn([]);

        $result = $this->gateway->createCase($dummyOrderId);
        $this->assertEquals(42, $result);
    }

    public function testCreateCaseCallsValidApiMethod()
    {
        $dummyOrderId = 1;
        $dummySignifydInvestigationId = 42;
        $this->createCaseBuilder
            ->method('build')
            ->willReturn([]);

        $this->apiClient
            ->expects($this->atLeastOnce())
            ->method('makeApiCall')
            ->with(
                $this->equalTo('/cases'),
                $this->equalTo('POST'),
                $this->isType('array')
            )
            ->willReturn([
                'investigationId' => $dummySignifydInvestigationId
            ]);

        $result = $this->gateway->createCase($dummyOrderId);
        $this->assertEquals(42, $result);
    }

    public function testCreateCaseNormalFlow()
    {
        $dummyOrderId = 1;
        $dummySignifydInvestigationId = 42;
        $this->createCaseBuilder
            ->method('build')
            ->willReturn([]);
        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'investigationId' => $dummySignifydInvestigationId
            ]);

        $returnedInvestigationId = $this->gateway->createCase($dummyOrderId);
        $this->assertEquals(
            $dummySignifydInvestigationId,
            $returnedInvestigationId,
            'Method must return value specified in "investigationId" response parameter'
        );
    }

    public function testCreateCaseWithFailedApiCall()
    {
        $dummyOrderId = 1;
        $apiCallFailureMessage = 'Api call failed';
        $this->createCaseBuilder
            ->method('build')
            ->willReturn([]);
        $this->apiClient
            ->method('makeApiCall')
            ->willThrowException(new ApiCallException($apiCallFailureMessage));

        $this->expectException(
            GatewayException::class,
            $apiCallFailureMessage
        );
        $this->gateway->createCase($dummyOrderId);
    }

    public function testCreateCaseWithMissedResponseRequiredData()
    {
        $dummyOrderId = 1;
        $this->createCaseBuilder
            ->method('build')
            ->willReturn([]);
        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'someOtherParameter' => 'foo',
            ]);

        $this->expectException(GatewayException::class);
        $this->gateway->createCase($dummyOrderId);
    }

    public function testCreateCaseWithAdditionalResponseData()
    {
        $dummyOrderId = 1;
        $dummySignifydInvestigationId = 42;
        $this->createCaseBuilder
            ->method('build')
            ->willReturn([]);
        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'investigationId' => $dummySignifydInvestigationId,
                'someOtherParameter' => 'foo',
            ]);

        $returnedInvestigationId = $this->gateway->createCase($dummyOrderId);
        $this->assertEquals(
            $dummySignifydInvestigationId,
            $returnedInvestigationId,
            'Method must return value specified in "investigationId" response parameter and ignore any other parameters'
        );
    }

    public function testSubmitCaseForGuaranteeCallsValidApiMethod()
    {
        $dummySygnifydCaseId = 42;
        $dummyDisposition = 'APPROVED';

        $this->apiClient
            ->expects($this->atLeastOnce())
            ->method('makeApiCall')
            ->with(
                $this->equalTo('/guarantees'),
                $this->equalTo('POST'),
                $this->equalTo([
                    'caseId' => $dummySygnifydCaseId
                ])
            )->willReturn([
                'disposition' => $dummyDisposition
            ]);

        $result = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
        $this->assertEquals('APPROVED', $result);
    }

    public function testSubmitCaseForGuaranteeWithFailedApiCall()
    {
        $dummySygnifydCaseId = 42;
        $apiCallFailureMessage = 'Api call failed';

        $this->apiClient
            ->method('makeApiCall')
            ->willThrowException(new ApiCallException($apiCallFailureMessage));

        $this->expectException(
            GatewayException::class,
            $apiCallFailureMessage
        );
        $result = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
        $this->assertEquals('Api call failed', $result);
    }

    public function testSubmitCaseForGuaranteeReturnsDisposition()
    {
        $dummySygnifydCaseId = 42;
        $dummyDisposition = 'APPROVED';
        $dummyGuaranteeId = 123;
        $dummyRereviewCount = 0;

        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'guaranteeId' => $dummyGuaranteeId,
                'disposition' => $dummyDisposition,
                'rereviewCount' => $dummyRereviewCount,
            ]);

        $actualDisposition = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
        $this->assertEquals(
            $dummyDisposition,
            $actualDisposition,
            'Method must return guarantee disposition retrieved in Signifyd API response as a result'
        );
    }

    public function testSubmitCaseForGuaranteeWithMissedDisposition()
    {
        $dummySygnifydCaseId = 42;
        $dummyGuaranteeId = 123;
        $dummyRereviewCount = 0;

        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'guaranteeId' => $dummyGuaranteeId,
                'rereviewCount' => $dummyRereviewCount,
            ]);

        $this->expectException(GatewayException::class);
        $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
    }

    public function testSubmitCaseForGuaranteeWithUnexpectedDisposition()
    {
        $dummySygnifydCaseId = 42;
        $dummyUnexpectedDisposition = 'UNEXPECTED';

        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'disposition' => $dummyUnexpectedDisposition,
            ]);

        $this->expectException(GatewayException::class);
        $result = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
        $this->assertEquals('UNEXPECTED', $result);
    }

    /**
     * @dataProvider supportedGuaranteeDispositionsProvider
     */
    public function testSubmitCaseForGuaranteeWithExpectedDisposition($dummyExpectedDisposition)
    {
        $dummySygnifydCaseId = 42;

        $this->apiClient
            ->method('makeApiCall')
            ->willReturn([
                'disposition' => $dummyExpectedDisposition,
            ]);

        try {
            $result = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
            $this->assertEquals($dummyExpectedDisposition, $result);
        } catch (GatewayException $e) {
            $this->fail(sprintf(
                'Expected disposition "%s" was not accepted with message "%s"',
                $dummyExpectedDisposition,
                $e->getMessage()
            ));
        }
    }

    /**
     * Checks a test case when guarantee for a case is successfully canceled
     *
     * @covers \Magento\Signifyd\Model\SignifydGateway\Gateway::cancelGuarantee
     */
    public function testCancelGuarantee()
    {
        $caseId = 123;

        $this->apiClient->expects(self::once())
            ->method('makeApiCall')
            ->with('/cases/' . $caseId . '/guarantee', 'PUT', ['guaranteeDisposition' => Gateway::GUARANTEE_CANCELED])
            ->willReturn(['disposition' => Gateway::GUARANTEE_CANCELED]);

        $result = $this->gateway->cancelGuarantee($caseId);
        self::assertEquals(Gateway::GUARANTEE_CANCELED, $result);
    }

    /**
     * Checks a case when API request returns unexpected guarantee disposition.
     *
     * @covers \Magento\Signifyd\Model\SignifydGateway\Gateway::cancelGuarantee
     * @expectedException \Magento\Signifyd\Model\SignifydGateway\GatewayException
     * @expectedExceptionMessage API returned unexpected disposition: DECLINED.
     */
    public function testCancelGuaranteeWithUnexpectedDisposition()
    {
        $caseId = 123;

        $this->apiClient->expects(self::once())
            ->method('makeApiCall')
            ->with('/cases/' . $caseId . '/guarantee', 'PUT', ['guaranteeDisposition' => Gateway::GUARANTEE_CANCELED])
            ->willReturn(['disposition' => Gateway::GUARANTEE_DECLINED]);

        $result = $this->gateway->cancelGuarantee($caseId);
        $this->assertEquals(Gateway::GUARANTEE_CANCELED, $result);
    }

    public function supportedGuaranteeDispositionsProvider()
    {
        return [
            'APPROVED' => ['APPROVED'],
            'DECLINED' => ['DECLINED'],
            'PENDING' => ['PENDING'],
            'CANCELED' => ['CANCELED'],
            'IN_REVIEW' => ['IN_REVIEW'],
            'UNREQUESTED' => ['UNREQUESTED'],
        ];
    }
}
