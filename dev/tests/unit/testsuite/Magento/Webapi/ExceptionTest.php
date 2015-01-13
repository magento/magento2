<?php
/**
 * Test Webapi module exception.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Webapi exception construct.
     */
    public function testConstruct()
    {
        $code = 1111;
        $details = ['key1' => 'value1', 'key2' => 'value2'];
        $apiException = new \Magento\Webapi\Exception(
            'Message',
            $code,
            \Magento\Webapi\Exception::HTTP_UNAUTHORIZED,
            $details
        );
        $this->assertEquals(
            $apiException->getHttpCode(),
            \Magento\Webapi\Exception::HTTP_UNAUTHORIZED,
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
        $this->setExpectedException('InvalidArgumentException', "The specified HTTP code \"{$httpCode}\" is invalid.");
        /** Create \Magento\Webapi\Exception object with invalid code. */
        /** Valid codes range is from 400 to 599. */
        new \Magento\Webapi\Exception('Message', 0, $httpCode);
    }

    public function testGetOriginatorSender()
    {
        $apiException = new \Magento\Webapi\Exception('Message', 0, \Magento\Webapi\Exception::HTTP_UNAUTHORIZED);
        /** Check that Webapi \Exception object with code 401 matches Sender originator.*/
        $this->assertEquals(
            \Magento\Webapi\Model\Soap\Fault::FAULT_CODE_SENDER,
            $apiException->getOriginator(),
            'Wrong Sender originator detecting.'
        );
    }

    public function testGetOriginatorReceiver()
    {
        $apiException = new \Magento\Webapi\Exception('Message', 0, \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR);
        /** Check that Webapi \Exception object with code 500 matches Receiver originator.*/
        $this->assertEquals(
            \Magento\Webapi\Model\Soap\Fault::FAULT_CODE_RECEIVER,
            $apiException->getOriginator(),
            'Wrong Receiver originator detecting.'
        );
    }

    /**
     * Data provider for testConstructInvalidCode.
     *
     * @return array
     */
    public function providerForTestConstructInvalidHttpCode()
    {
        //Each array contains invalid \Exception code.
        return [[300], [600]];
    }
}
