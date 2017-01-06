<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Controller\Webhooks;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Model\CaseUpdatingServiceFactory;
use Magento\Signifyd\Model\CaseUpdatingService;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestValidator;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageReader;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessage;
use Psr\Log\LoggerInterface;
use Magento\Signifyd\Controller\Webhooks\Handler;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;

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
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var ResponseHttp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var WebhookRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webhookRequest;

    /**
     * @var WebhookMessageReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webhookMessageReader;

    /**
     * @var WebhookRequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webhookRequestValidator;

    /**
     * @var CaseUpdatingServiceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $caseUpdatingServiceFactory;

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
        $this->caseUpdatingServiceFactory = $this->getMockBuilder(CaseUpdatingServiceFactory::class)
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

        $this->controller = new Handler(
            $this->context,
            $this->webhookRequest,
            $this->logger,
            $this->webhookMessageReader,
            $this->caseUpdatingServiceFactory,
            $this->webhookRequestValidator
        );
    }

    /**
     * Successfull case
     */
    public function testExecuteSuccessfully()
    {
        $eventTopic = 'cases\test';
        $data = ['score' => 200, 'caseId' => 1];

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

        $caseUpdatingService = $this->getMockBuilder(CaseUpdatingService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $caseUpdatingService->expects($this->once())
            ->method('update')
            ->with($data)
            ->willReturn($caseUpdatingService);

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
        $data = ['score' => 200, 'caseId' => 1];

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

        $caseUpdatingService = $this->getMockBuilder(CaseUpdatingService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $caseUpdatingService->expects($this->once())
            ->method('update')
            ->with($data)
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
}
