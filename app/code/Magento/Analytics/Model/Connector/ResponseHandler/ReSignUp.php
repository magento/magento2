<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\Subscription;
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
     * @var Subscription
     */
    private $subscription;

    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param Subscription $subscription
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Subscription $subscription,
        SubscriptionStatusProvider $subscriptionStatusProvider
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->subscription = $subscription;
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
    }

    /**
     * @inheritdoc
     */
    public function handleResponse(array $responseBody)
    {
        if ($this->subscriptionStatusProvider->getStatus() === SubscriptionStatusProvider::ENABLED) {
            $this->analyticsToken->storeToken(null);
            $this->subscription->retry();
        }
        return false;
    }
}
