<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\OTP;
use PHPUnit\Framework\TestCase;

class OTPTest extends TestCase
{
    public function testHandleResult()
    {
        $OTPHandler = new OTP();
        $this->assertFalse($OTPHandler->handleResponse([]));
        $expectedOtp = 123;
        $this->assertEquals($expectedOtp, $OTPHandler->handleResponse(['otp' => $expectedOtp]));
    }
}
