<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> upstream/2.2-develop
namespace Magento\Braintree\Test\Unit\Gateway;

use Braintree\Result\Successful;
use Braintree\Transaction;
<<<<<<< HEAD
=======
use InvalidArgumentException;
>>>>>>> upstream/2.2-develop
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

<<<<<<< HEAD
    /**
     * @inheritdoc
     */
=======
>>>>>>> upstream/2.2-develop
    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
<<<<<<< HEAD
     * @covers \Magento\Braintree\Gateway\SubjectReader::readCustomerId
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     * @return void
     */
    public function testReadCustomerIdWithException(): void
=======
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testReadCustomerIdWithException()
>>>>>>> upstream/2.2-develop
    {
        $this->subjectReader->readCustomerId([]);
    }

<<<<<<< HEAD
    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readCustomerId
     * @return void
     */
    public function testReadCustomerId(): void
    {
        $customerId = 1;
        $this->assertEquals($customerId, $this->subjectReader->readCustomerId(['customer_id' => $customerId]));
    }

    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readPublicHash
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "public_hash" field does not exists
     * @return void
     */
    public function testReadPublicHashWithException(): void
=======
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
>>>>>>> upstream/2.2-develop
    {
        $this->subjectReader->readPublicHash([]);
    }

<<<<<<< HEAD
    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readPublicHash
     * @return void
     */
    public function testReadPublicHash(): void
    {
        $hash = 'fj23djf2o1fd';
        $this->assertEquals($hash, $this->subjectReader->readPublicHash(['public_hash' => $hash]));
    }

    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readPayPal
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Transaction has't paypal attribute
     * @return void
     */
    public function testReadPayPalWithException(): void
    {
        $transaction = Transaction::factory([
            'id' => 'u38rf8kg6vn',
=======
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
>>>>>>> upstream/2.2-develop
        ]);
        $this->subjectReader->readPayPal($transaction);
    }

<<<<<<< HEAD
    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readPayPal
     * @return void
     */
    public function testReadPayPal(): void
    {
        $paypal = [
            'paymentId' => '3ek7dk7fn0vi1',
            'payerEmail' => 'payer@example.com',
        ];
        $transaction = Transaction::factory([
            'id' => '4yr95vb',
            'paypal' => $paypal,
        ]);

        $this->assertEquals($paypal, $this->subjectReader->readPayPal($transaction));
=======
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
>>>>>>> upstream/2.2-develop
    }

    /**
     * Checks a case when subject reader retrieves successful Braintree transaction.
<<<<<<< HEAD
     *
     * @return void
     */
    public function testReadTransaction(): void
    {
        $transaction = Transaction::factory(['id' => 1]);
        $response = [
            'object' => new Successful($transaction, 'transaction'),
        ];
        $actual = $this->subjectReader->readTransaction($response);

        $this->assertSame($transaction, $actual);
=======
     */
    public function testReadTransaction()
    {
        $transaction = Transaction::factory(['id' => 1]);
        $response = [
            'object' => new Successful($transaction, 'transaction')
        ];

        $actual = $this->subjectReader->readTransaction($response);
        self::assertSame($transaction, $actual);
>>>>>>> upstream/2.2-develop
    }

    /**
     * Checks a case when subject reader retrieves invalid data instead transaction details.
     *
     * @param array $response
     * @param string $expectedMessage
     * @dataProvider invalidTransactionResponseDataProvider
<<<<<<< HEAD
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testReadTransactionWithInvalidResponse(array $response, string $expectedMessage): void
    {
        $this->expectExceptionMessage($expectedMessage);
=======
     * @expectedException InvalidArgumentException
     */
    public function testReadTransactionWithInvalidResponse(array $response, string $expectedMessage)
    {
        self::expectExceptionMessage($expectedMessage);
>>>>>>> upstream/2.2-develop
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
<<<<<<< HEAD
                    'object' => [],
                ],
                'expectedMessage' => 'Response object does not exist.',
            ],
            [
                'response' => [
                    'object' => new \stdClass(),
                ],
                'expectedMessage' => 'The object is not a class \Braintree\Transaction.',
            ],
            [
                'response' => [
                    'object' => $response,
                ],
                'expectedMessage' => 'The object is not a class \Braintree\Transaction.',
            ],
=======
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
>>>>>>> upstream/2.2-develop
        ];
    }
}
