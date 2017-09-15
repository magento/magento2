<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\ResponseHandler\SignUp;

/**
 * Class SignUpTest
 */
class SignUpTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $accessToken = 'access-token-123';
        $analyticsToken = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();
        $analyticsToken->expects($this->once())
            ->method('storeToken')
            ->with($accessToken);
        $signUpHandler = new SignUp($analyticsToken, new JsonConverter());
        $this->assertFalse($signUpHandler->handleResponse([]));
        $this->assertEquals($accessToken, $signUpHandler->handleResponse(['access-token' => $accessToken]));
    }
}
