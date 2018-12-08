<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\OTP;

<<<<<<< HEAD
/**
 * Class OTPTest
 */
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
class OTPTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $OTPHandler = new OTP();
        $this->assertFalse($OTPHandler->handleResponse([]));
        $expectedOtp = 123;
        $this->assertEquals($expectedOtp, $OTPHandler->handleResponse(['otp' => $expectedOtp]));
    }
}
