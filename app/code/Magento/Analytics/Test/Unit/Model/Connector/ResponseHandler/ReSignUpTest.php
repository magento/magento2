<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\ResponseHandler\ReSignUp;
use Magento\Analytics\Model\Subscription;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Class ReSignUpTest
 */
class ReSignUpTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleResult()
    {
        $analyticsToken = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $analyticsToken->expects($this->once())
            ->method('storeToken')
            ->with(null);
        $subscription = $this->getMockBuilder(Subscription::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subscriptionStatusProvider = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subscriptionStatusProvider->method('getStatus')->willReturn(SubscriptionStatusProvider::ENABLED);
        $reSignUpHandler = new ReSignUp($analyticsToken, $subscription, $subscriptionStatusProvider);
        $this->assertFalse($reSignUpHandler->handleResponse([]));
    }
}
