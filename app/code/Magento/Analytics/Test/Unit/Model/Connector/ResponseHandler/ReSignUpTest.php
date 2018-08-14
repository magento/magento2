<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector\ResponseHandler\ReSignUp;
use Magento\Analytics\Model\SubscriptionStatusProvider;

/**
 * Class ReSignUpTest
 */
class ReSignUpTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $analyticsToken = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $analyticsToken->expects($this->once())
            ->method('storeToken')
            ->with(null);
        $subscriptionHandler = $this->getMockBuilder(SubscriptionHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subscriptionStatusProvider = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subscriptionStatusProvider->method('getStatus')->willReturn(SubscriptionStatusProvider::ENABLED);
        $reSignUpHandler = new ReSignUp($analyticsToken, $subscriptionHandler, $subscriptionStatusProvider);
        $this->assertFalse($reSignUpHandler->handleResponse([]));
    }
}
