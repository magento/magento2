<?php
/**
 * Test Webapi module exception.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit;

use Magento\Framework\Webapi\Exception;
use Magento\Webapi\Model\Soap\Fault;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * Test Webapi exception construct.
     */
    public function testConstruct()
    {
        $code = 1111;
        $details = ['key1' => 'value1', 'key2' => 'value2'];
        $apiException = new Exception(
            __('Message'),
            $code,
            Exception::HTTP_UNAUTHORIZED,
            $details
        );
        $this->assertEquals(
            $apiException->getHttpCode(),
            Exception::HTTP_UNAUTHORIZED,
            'Exception code is set incorrectly in construct.'
        );
        $this->assertEquals(
            $apiException->getMessage(),
            'Message',
            'Exception message is set incorrectly in construct.'
        );
        $this->assertEquals($apiException->getCode(), $code, 'Exception code is set incorrectly in construct.');
        $this->assertEquals($apiException->getDetails(), $details, 'Details are set incorrectly in construct.');
    }

    /**
     * Test Webapi exception construct with invalid data.
     *
     * @dataProvider providerForTestConstructInvalidHttpCode
     */
    public function testConstructInvalidHttpCode($httpCode)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("The specified HTTP code \"{$httpCode}\" is invalid.");
        /** Create \Magento\Framework\Webapi\Exception object with invalid code. */
        /** Valid codes range is from 400 to 599. */
        new Exception(__('Message'), 0, $httpCode);
    }

    public function testGetOriginatorSender()
    {
        $apiException = new Exception(
            __('Message'),
            0,
            Exception::HTTP_UNAUTHORIZED
        );
        /** Check that Webapi \Exception object with code 401 matches Sender originator.*/
        $this->assertEquals(
            Fault::FAULT_CODE_SENDER,
            $apiException->getOriginator(),
            'Wrong Sender originator detecting.'
        );
    }

    public function testGetOriginatorReceiver()
    {
        $apiException = new Exception(
            __('Message'),
            0,
            Exception::HTTP_INTERNAL_ERROR
        );
        /** Check that Webapi \Exception object with code 500 matches Receiver originator.*/
        $this->assertEquals(
            Fault::FAULT_CODE_RECEIVER,
            $apiException->getOriginator(),
            'Wrong Receiver originator detecting.'
        );
    }

    /**
     * Data provider for testConstructInvalidCode.
     *
     * @return array
     */
    public static function providerForTestConstructInvalidHttpCode()
    {
        //Each array contains invalid \Exception code.
        return [[300], [600]];
    }
}
