<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Analytics\Model\Connector\ResponseHandler\ReSignUp;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use PHPUnit\Framework\TestCase;

class ReSignUpTest extends TestCase
{
    public function testHandleResult()
    {
        $analyticsToken = $this->createMock(AnalyticsToken::class);
        $analyticsToken->expects($this->once())
            ->method('storeToken')
            ->with(null);
        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionStatusProvider = $this->createMock(SubscriptionStatusProvider::class);
        $subscriptionStatusProvider->method('getStatus')->willReturn(SubscriptionStatusProvider::ENABLED);
        $reSignUpHandler = new ReSignUp($analyticsToken, $subscriptionHandler, $subscriptionStatusProvider);
        $this->assertFalse($reSignUpHandler->handleResponse([]));
    }
}
