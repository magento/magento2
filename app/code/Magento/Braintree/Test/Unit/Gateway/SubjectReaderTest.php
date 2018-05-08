<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway;

use Braintree\Result\Successful;
use Braintree\Transaction;
use InvalidArgumentException;
use Magento\Braintree\Gateway\SubjectReader;

/**
 * Class SubjectReaderTest
 */
class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testReadCustomerIdWithException()
    {
        $this->subjectReader->readCustomerId([]);
    }

    public function testReadCustomerId()
    {
        $customerId = 1;
        self::assertEquals($customerId, $this->subjectReader->readCustomerId(['customer_id' => $customerId]));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "public_hash" field does not exists
     */
    public function testReadPublicHashWithException()
    {
        $this->subjectReader->readPublicHash([]);
    }

    public function testReadPublicHash()
    {
        $hash = 'fj23djf2o1fd';
        self::assertEquals($hash, $this->subjectReader->readPublicHash(['public_hash' => $hash]));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Transaction has't paypal attribute
     */
    public function testReadPayPalWithException()
    {
        $transaction = Transaction::factory([
            'id' => 'u38rf8kg6vn'
        ]);
        $this->subjectReader->readPayPal($transaction);
    }

    public function testReadPayPal()
    {
        $paypal = [
            'paymentId' => '3ek7dk7fn0vi1',
            'payerEmail' => 'payer@example.com'
        ];
        $transaction = Transaction::factory([
            'id' => '4yr95vb',
            'paypal' => $paypal
        ]);

        self::assertEquals($paypal, $this->subjectReader->readPayPal($transaction));
    }

    /**
     * Checks a case when subject reader retrieves successful Braintree transaction.
     */
    public function testReadTransaction()
    {
        $transaction = Transaction::factory(['id' => 1]);
        $response = [
            'object' => new Successful($transaction, 'transaction')
        ];

        $actual = $this->subjectReader->readTransaction($response);
        self::assertSame($transaction, $actual);
    }

    /**
     * Checks a case when subject reader retrieves invalid data instead transaction details.
     *
     * @param array $response
     * @param string $expectedMessage
     * @dataProvider invalidTransactionResponseDataProvider
     * @expectedException InvalidArgumentException
     */
    public function testReadTransactionWithInvalidResponse(array $response, string $expectedMessage)
    {
        self::expectExceptionMessage($expectedMessage);
        $this->subjectReader->readTransaction($response);
    }

    /**
     * Gets list of variations with invalid subject data.
     *
     * @return array
     */
    public function invalidTransactionResponseDataProvider(): array
    {
        $transaction = new \stdClass();
        $response = new \stdClass();
        $response->transaction = $transaction;

        return [
            [
                'response' => [
                    'object' => []
                ],
                'expectedMessage' => 'Response object does not exist.'
            ],
            [
                'response' => [
                    'object' => new \stdClass()
                ],
                'expectedMessage' => 'The object is not a class \Braintree\Transaction.'
            ],
            [
                'response' => [
                    'object' => $response
                ],
                'expectedMessage' => 'The object is not a class \Braintree\Transaction.'
            ]
        ];
    }
}
