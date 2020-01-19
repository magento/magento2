<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Webhooks;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\CaseServices\UpdatingServiceFactory;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookMessageReader;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestValidator;
use Psr\Log\LoggerInterface;

/**
 * Responsible for handling webhook posts from Signifyd service.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks/
 */
class Handler extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * Event topic of test webhook request.
     *
     * @var string
     */
    private static $eventTopicTest = 'cases/test';

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
     * @var UpdatingServiceFactory
     */
    private $caseUpdatingServiceFactory;

    /**
     * @var WebhookRequestValidator
     */
    private $webhookRequestValidator;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param WebhookRequest $webhookRequest
     * @param LoggerInterface $logger
     * @param WebhookMessageReader $webhookMessageReader
     * @param UpdatingServiceFactory $caseUpdatingServiceFactory
     * @param WebhookRequestValidator $webhookRequestValidator
     * @param CaseRepositoryInterface $caseRepository
     * @param Config $config
     */
    public function __construct(
        Context $context,
        WebhookRequest $webhookRequest,
        LoggerInterface $logger,
        WebhookMessageReader $webhookMessageReader,
        UpdatingServiceFactory $caseUpdatingServiceFactory,
        WebhookRequestValidator $webhookRequestValidator,
        CaseRepositoryInterface $caseRepository,
        Config $config
    ) {
        parent::__construct($context);
        $this->webhookRequest = $webhookRequest;
        $this->logger = $logger;
        $this->webhookMessageReader = $webhookMessageReader;
        $this->caseUpdatingServiceFactory = $caseUpdatingServiceFactory;
        $this->webhookRequestValidator = $webhookRequestValidator;
        $this->caseRepository = $caseRepository;
        $this->config = $config;
    }

    /**
     * Processes webhook request data and updates case entity
     *
     * @return void
     */
    public function execute()
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->logger->debug($this->webhookRequest->getEventTopic() . '|' . $this->webhookRequest->getBody());
        }

        if (!$this->webhookRequestValidator->validate($this->webhookRequest)) {
            $this->_redirect('noroute');
            return;
        }

        $webhookMessage = $this->webhookMessageReader->read($this->webhookRequest);
        if ($webhookMessage->getEventTopic() === self::$eventTopicTest) {
            return;
        }

        $data = $webhookMessage->getData();
        if (empty($data['caseId'])) {
            $this->_redirect('noroute');
            return;
        }

        $case = $this->caseRepository->getByCaseId($data['caseId']);
        if ($case === null) {
            $this->_redirect('noroute');
            return;
        }

        $caseUpdatingService = $this->caseUpdatingServiceFactory->create($webhookMessage->getEventTopic());
        try {
            $caseUpdatingService->update($case, $data);
        } catch (LocalizedException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->critical($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
