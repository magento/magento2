<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\AnalyticsToken;
<<<<<<< HEAD
use Magento\Analytics\Model\Connector\Http\JsonConverter;
use Magento\Analytics\Model\Connector\ResponseHandler\SignUp;

/**
 * Class SignUpTest
 */
=======
use Magento\Analytics\Model\Connector\ResponseHandler\SignUp;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
        $signUpHandler = new SignUp($analyticsToken, new JsonConverter());
=======
        $objectManager = new ObjectManager($this);
        $signUpHandler = $objectManager->getObject(
            SignUp::class,
            ['analyticsToken' => $analyticsToken]
        );
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->assertFalse($signUpHandler->handleResponse([]));
        $this->assertEquals($accessToken, $signUpHandler->handleResponse(['access-token' => $accessToken]));
    }
}
