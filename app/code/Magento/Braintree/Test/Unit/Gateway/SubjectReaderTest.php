<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway;

use Braintree\Result\Successful;
use Braintree\Transaction;
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \Magento\Braintree\Gateway\SubjectReader::readCustomerId
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     * @return void
     */
    public function testReadCustomerIdWithException(): void
    {
        $this->subjectReader->readCustomerId([]);
    }

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
    {
        $this->subjectReader->readPublicHash([]);
    }

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
        ]);
        $this->subjectReader->readPayPal($transaction);
    }

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
    }

    /**
     * Checks a case when subject reader retrieves successful Braintree transaction.
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
    }

    /**
     * Checks a case when subject reader retrieves invalid data instead transaction details.
     *
     * @param array $response
     * @param string $expectedMessage
     * @dataProvider invalidTransactionResponseDataProvider
     * @expectedException \InvalidArgumentException
     * @return void
     */
    public function testReadTransactionWithInvalidResponse(array $response, string $expectedMessage): void
    {
        $this->expectExceptionMessage($expectedMessage);
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
        ];
    }
}
