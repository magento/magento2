<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;
use Magento\Analytics\Model\Subscription;

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
     * @param AnalyticsToken $analyticsToken
     * @param Subscription $subscription
     */
    public function __construct(AnalyticsToken $analyticsToken, Subscription $subscription)
    {
        $this->analyticsToken = $analyticsToken;
        $this->subscription = $subscription;
    }

    /**
     * @param array $responseBody
     *
     * @return bool|string
     */
    public function handleResponse(array $responseBody)
    {
        $this->analyticsToken->storeToken(null);
        $this->subscription->retry();
        return false;
    }
}
