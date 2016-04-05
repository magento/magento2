<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Api;

use Magento\Paypal\Model\Api\ProcessableException;

class ProcessableExceptionTest extends \PHPUnit_Framework_TestCase
{
    const UNKNOWN_CODE = 10411;

    /**
     * @var ProcessableException
     */
    private $model;

    /**
     * @param int $code
     * @param string $msg
     * @return void
     * @dataProvider getUserMessageDataProvider
     */
    public function testGetUserMessage($code, $msg)
    {
        $this->model = new ProcessableException(__($msg), null, $code);
        $this->assertEquals($msg, $this->model->getUserMessage());
    }

    /**
     * @return array
     */
    public function getUserMessageDataProvider()
    {
        return [
            [
                ProcessableException::API_INTERNAL_ERROR,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you.",
            ],
            [
                ProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
                "I'm sorry - but we were not able to process your payment. "
                . "Please try another payment method or contact us so we can assist you."
            ],
            [
                ProcessableException::API_COUNTRY_FILTER_DECLINE,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                ProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                ProcessableException::API_OTHER_FILTER_DECLINE,
                "I'm sorry - but we are not able to complete your transaction. Please contact us so we can assist you."
            ],
            [
                ProcessableException::API_ADDRESS_MATCH_FAIL,
                'A match of the Shipping Address City, State, and Postal Code failed.'
            ],
            [
                self::UNKNOWN_CODE,
                "We can't place the order."
            ]
        ];
    }
}
