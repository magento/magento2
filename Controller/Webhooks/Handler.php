<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Webhooks;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Signifyd\Model\CaseUpdatingService;
use Magento\Signifyd\Model\CaseUpdatingServiceFactory;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequest;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookRequestReader;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookException;
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
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WebhookRequestReader
     */
    private $webhookRequestReader;

    /**
     * @var CaseUpdatingServiceFactory
     */
    private $caseUpdatingServiceFactory;

    /**
     * @param Context $context
     * @param WebhookRequest $webhookRequest
     * @param Config $config
     * @param LoggerInterface $logger
     * @param WebhookRequestReader $webhookRequestReader
     * @param CaseUpdatingServiceFactory $caseUpdatingServiceFactory
     */
    public function __construct(
        Context $context,
        WebhookRequest $webhookRequest,
        Config $config,
        LoggerInterface $logger,
        WebhookRequestReader $webhookRequestReader,
        CaseUpdatingServiceFactory $caseUpdatingServiceFactory
    ) {
        parent::__construct($context);
        $this->webhookRequest = $webhookRequest;
        $this->config = $config;
        $this->logger = $logger;
        $this->webhookRequestReader = $webhookRequestReader;
        $this->caseUpdatingServiceFactory = $caseUpdatingServiceFactory;
    }

    /**
     * Processes webhook message data and updates case entity
     *
     * @return void
     */
    public function execute()
    {
        if ($this->config->isActive() === false) {
            return;
        }

        try {
            $webhookMessage = $this->webhookRequestReader->read($this->webhookRequest);
            $caseUpdatingService = $this->caseUpdatingServiceFactory->create($webhookMessage->getEventTopic());
            $caseUpdatingService->update($webhookMessage->getData());
        } catch (WebhookException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->error($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->error($e->getMessage());
        }
    }
}
