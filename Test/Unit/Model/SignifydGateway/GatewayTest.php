<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model\SignifydGateway;

use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Signifyd\Model\SignifydGateway\Gateway;
use Magento\Signifyd\Model\SignifydGateway\GatewayException;
use Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilderInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;

class GatewayTest extends TestCase
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

        $om = new ObjectManager($this);
        $this->gateway = $om->getObject(Gateway::class, [
            'createCaseBuilder' => $this->createCaseBuilder,
            'apiClient' => $this->apiClient,
        ]);
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

        $this->gateway->createCase($dummyOrderId);
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

        $this->gateway->createCase($dummyOrderId);

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

        $this->setExpectedException(
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

        $this->setExpectedException(GatewayException::class);
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

        $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);

    }

    public function testSubmitCaseForGuaranteeWithFailedApiCall()
    {
        $dummySygnifydCaseId = 42;
        $apiCallFailureMessage = 'Api call failed';

        $this->apiClient
            ->method('makeApiCall')
            ->willThrowException(new ApiCallException($apiCallFailureMessage));

        $this->setExpectedException(
            GatewayException::class,
            $apiCallFailureMessage
        );
        $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
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

        $this->setExpectedException(GatewayException::class);
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

        $this->setExpectedException(GatewayException::class);
        $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
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

        $actualDisposition = $this->gateway->submitCaseForGuarantee($dummySygnifydCaseId);
        $this->assertEquals(
            $dummyExpectedDisposition,
            $actualDisposition,
            'Expected disposition should be return from method'
        );
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
