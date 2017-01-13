<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Controller\Webhooks;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Controller\Webhooks\Handler;
use Magento\Signifyd\Model\CaseServices\UpdatingService;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessage;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageReader;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class IndexTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Handler
     */
    private $controller;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @var ResponseHttp|MockObject
     */
    private $response;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var WebhookRequest|MockObject
     */
    private $webhookRequest;

    /**
     * @var WebhookMessageReader|MockObject
     */
    private $webhookMessageReader;

    /**
     * @var WebhookRequestValidator|MockObject
     */
    private $webhookRequestValidator;

    /**
     * @var UpdatingServiceFactory|MockObject
     */
    private $caseUpdatingServiceFactory;

    /**
     * @var CaseRepositoryInterface|MockObject
     */
    private $caseRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->webhookRequest = $this->getMockBuilder(WebhookRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->webhookMessageReader = $this->getMockBuilder(WebhookMessageReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->webhookRequestValidator = $this->getMockBuilder(WebhookRequestValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->caseUpdatingServiceFactory = $this->getMockBuilder(UpdatingServiceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->response = $this->getMockBuilder(ResponseHttp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->redirect = $this->getMockBuilder(RedirectInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $this->caseRepository = $this->getMockBuilder(CaseRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCaseId'])
            ->getMockForAbstractClass();

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isDebugModeEnabled'])
            ->getMock();
        $config->expects(self::any())
            ->method('getByCaseId')
            ->willReturn(false);

        $this->controller = new Handler(
            $this->context,
            $this->webhookRequest,
            $this->logger,
            $this->webhookMessageReader,
            $this->caseUpdatingServiceFactory,
            $this->webhookRequestValidator,
            $this->caseRepository,
            $config
        );
    }

    /**
     * Successfull case
     */
    public function testExecuteSuccessfully()
    {
        $eventTopic = 'cases\test';
        $caseId = 1;
        $data = ['score' => 200, 'caseId' => $caseId];

        $this->webhookRequestValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $webhookMessage = $this->getMockBuilder(WebhookMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webhookMessage->expects($this->once())
            ->method('getEventTopic')
            ->willReturn($eventTopic);
        $webhookMessage->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $this->webhookMessageReader->expects($this->once())
            ->method('read')
            ->with($this->webhookRequest)
            ->willReturn($webhookMessage);

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with(self::equalTo($caseId))
            ->willReturn($caseEntity);

        $caseUpdatingService = $this->getMockBuilder(UpdatingService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $caseUpdatingService->expects($this->once())
            ->method('update')
            ->with($caseEntity, $data);

        $this->caseUpdatingServiceFactory->expects($this->once())
            ->method('create')
            ->with($eventTopic)
            ->willReturn($caseUpdatingService);

        $this->controller->execute();
    }

    /**
     * Case when there is exception while updating case
     */
    public function testExecuteCaseUpdatingServiceException()
    {
        $eventTopic = 'cases\test';
        $caseId = 1;
        $data = ['score' => 200, 'caseId' => $caseId];

        $this->webhookRequestValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $webhookMessage = $this->getMockBuilder(WebhookMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $webhookMessage->expects($this->once())
            ->method('getEventTopic')
            ->willReturn($eventTopic);
        $webhookMessage->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $this->webhookMessageReader->expects($this->once())
            ->method('read')
            ->with($this->webhookRequest)
            ->willReturn($webhookMessage);

        $caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with(self::equalTo($caseId))
            ->willReturn($caseEntity);

        $caseUpdatingService = $this->getMockBuilder(UpdatingService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $caseUpdatingService->expects($this->once())
            ->method('update')
            ->with($caseEntity, $data)
            ->willThrowException(new LocalizedException(__('Error')));

        $this->caseUpdatingServiceFactory->expects($this->once())
            ->method('create')
            ->with($eventTopic)
            ->willReturn($caseUpdatingService);

        $this->response->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(400);
        $this->logger->expects($this->once())
            ->method('critical');

        $this->controller->execute();
    }

    /**
     * Case when webhook request validation fails
     */
    public function testExecuteRequestValidationFails()
    {
        $this->webhookRequestValidator->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->response, 'noroute', []);
        $this->webhookMessageReader->expects($this->never())
            ->method('read');
        $this->caseUpdatingServiceFactory->expects($this->never())
            ->method('create');

        $this->controller->execute();
    }

    /**
     * Checks a test case when received input data does not contain Signifyd case id.
     *
     * @covers \Magento\Signifyd\Controller\Webhooks\Handler::execute
     */
    public function testExecuteWithMissedCaseId()
    {
        $this->webhookRequestValidator->expects(self::once())
            ->method('validate')
            ->willReturn(true);

        $webhookMessage = $this->getMockBuilder(WebhookMessage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $webhookMessage->expects(self::once())
            ->method('getData')
            ->willReturn([
                'orderId' => '1000101'
            ]);

        $this->webhookMessageReader->expects(self::once())
            ->method('read')
            ->with($this->webhookRequest)
            ->willReturn($webhookMessage);

        $this->redirect->expects(self::once())
            ->method('redirect')
            ->with($this->response, 'noroute', []);

        $this->controller->execute();
    }

    /**
     * Checks a case when Signifyd case entity not found.
     *
     * @covers \Magento\Signifyd\Controller\Webhooks\Handler::execute
     */
    public function testExecuteWithNotFoundCaseEntity()
    {
        $caseId = 123;

        $this->webhookRequestValidator->expects(self::once())
            ->method('validate')
            ->willReturn(true);

        $webhookMessage = $this->getMockBuilder(WebhookMessage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $webhookMessage->expects(self::once())
            ->method('getData')
            ->willReturn([
                'orderId' => '1000101',
                'caseId' => $caseId
            ]);

        $this->webhookMessageReader->expects(self::once())
            ->method('read')
            ->with($this->webhookRequest)
            ->willReturn($webhookMessage);

        $this->caseRepository->expects(self::once())
            ->method('getByCaseId')
            ->with(self::equalTo($caseId))
            ->willReturn(null);

        $this->redirect->expects(self::once())
            ->method('redirect')
            ->with($this->response, 'noroute', []);

        $this->controller->execute();
    }
}
