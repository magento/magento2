<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Webhooks;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Signifyd\Model\Config;
use Magento\Signifyd\Model\SignifydGateway\Response\RawRequestBody;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookFactory;
use Magento\Signifyd\Model\SignifydGateway\Response\WebhookException;
use Psr\Log\LoggerInterface;

/**
 * Responsible for handling webhook posts from Signifyd service.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks/
 */
class Index extends Action
{
    /**
     * @var RawRequestBody
     */
    private $rawRequestBody;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var WebhookFactory
     */
    private $webhookFactory;

    /**
     * @param Context $context
     * @param RawRequestBody $rawRequestBody
     * @param Config $config
     * @param LoggerInterface $logger
     * @param WebhookFactory $webhookFactory
     */
    public function __construct(
        Context $context,
        RawRequestBody $rawRequestBody,
        Config $config,
        LoggerInterface $logger,
        WebhookFactory $webhookFactory
    ) {
        parent::__construct($context);
        $this->rawRequestBody = $rawRequestBody;
        $this->config = $config;
        $this->logger = $logger;
        $this->webhookFactory = $webhookFactory;
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

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $hash = $request->getHeader('X-SIGNIFYD-SEC-HMAC-SHA256');
        $topic = $request->getHeader('X-SIGNIFYD-TOPIC');
        $rawResponseBody =  $this->rawRequestBody->get();

        try {
            $webhook = $this->webhookFactory->create($rawResponseBody, $hash, $topic);
            if ($webhook->isTest()) {
                return;
            }
        } catch (WebhookException $e) {
            $this->getResponse()->setHttpResponseCode(400);
            $this->logger->error($e->getMessage());
        }
    }
}
