<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\ResponseHandler\SignUp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SignUpTest extends TestCase
{
    public function testHandleResult()
    {
        $accessToken = 'access-token-123';
        $analyticsToken = $this->createMock(AnalyticsToken::class);
        $analyticsToken->expects($this->once())
            ->method('storeToken')
            ->with($accessToken);
        $objectManager = new ObjectManager($this);
        $signUpHandler = $objectManager->getObject(
            SignUp::class,
            ['analyticsToken' => $analyticsToken]
        );
        $this->assertFalse($signUpHandler->handleResponse([]));
        $this->assertEquals($accessToken, $signUpHandler->handleResponse(['access-token' => $accessToken]));
    }
}
