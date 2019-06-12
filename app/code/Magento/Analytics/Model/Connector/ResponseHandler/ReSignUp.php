<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Removes stored token and triggers subscription process.
 */
class ReSignUp implements ResponseHandlerInterface
{
    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;
    
    /**
     * @var SubscriptionHandler
     */
    private $subscriptionHandler;

    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param SubscriptionHandler $subscriptionHandler
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        SubscriptionHandler $subscriptionHandler,
        SubscriptionStatusProvider $subscriptionStatusProvider
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->subscriptionHandler = $subscriptionHandler;
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
    }

    /**
     * @inheritdoc
     */
    public function handleResponse(array $responseBody)
    {
        if ($this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->analyticsToken->storeToken(null);
            $this->subscriptionHandler->processEnabled();
        }
        return false;
    }
}
