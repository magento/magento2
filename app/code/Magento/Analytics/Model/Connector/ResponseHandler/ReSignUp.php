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
 * @since 2.2.0
 */
class ReSignUp implements ResponseHandlerInterface
{
    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;
    
    /**
     * @var SubscriptionHandler
     * @since 2.2.0
     */
    private $subscriptionHandler;

    /**
     * @var SubscriptionStatusProvider
     * @since 2.2.0
     */
    private $subscriptionStatusProvider;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param SubscriptionHandler $subscriptionHandler
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     * @since 2.2.0
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
     * @since 2.2.0
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
