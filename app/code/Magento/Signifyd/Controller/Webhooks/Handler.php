<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Webhooks;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Model\CaseUpdatingServiceFactory;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestValidator;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageReader;
use Psr\Log\LoggerInterface;

/**
 * Responsible for handling webhook posts from Signifyd service.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks/
 */
class Handler extends Action
{
    /**
     * @var WebhookRequest
     */
    private $webhookRequest;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WebhookMessageReader
     */
    private $webhookMessageReader;

    /**
     * @var CaseUpdatingServiceFactory
     */
    private $caseUpdatingServiceFactory;

    /**
     * @var WebhookRequestValidator
     */
    private $webhookRequestValidator;

    /**
     * @param Context $context
     * @param WebhookRequest $webhookRequest
     * @param LoggerInterface $logger
     * @param WebhookMessageReader $webhookMessageReader
     * @param CaseUpdatingServiceFactory $caseUpdatingServiceFactory
     * @param WebhookRequestValidator $webhookRequestValidator
     */
    public function __construct(
        Context $context,
        WebhookRequest $webhookRequest,
        LoggerInterface $logger,
        WebhookMessageReader $webhookMessageReader,
        CaseUpdatingServiceFactory $caseUpdatingServiceFactory,
        WebhookRequestValidator $webhookRequestValidator
    ) {
        parent::__construct($context);
        $this->webhookRequest = $webhookRequest;
        $this->logger = $logger;
        $this->webhookMessageReader = $webhookMessageReader;
        $this->caseUpdatingServiceFactory = $caseUpdatingServiceFactory;
        $this->webhookRequestValidator = $webhookRequestValidator;
    }

    /**
     * Processes webhook request data and updates case entity
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->webhookRequestValidator->validate($this->webhookRequest)) {
            $this->_redirect('noroute');
            return;
        }

        $webhookMessage = $this->webhookMessageReader->read($this->webhookRequest);
        $caseUpdatingService = $this->caseUpdatingServiceFactory->create($webhookMessage->getEventTopic());

        try {
            $caseUpdatingService->update($webhookMessage->getData());
        } catch (LocalizedException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->critical($e);
        }
    }
}
