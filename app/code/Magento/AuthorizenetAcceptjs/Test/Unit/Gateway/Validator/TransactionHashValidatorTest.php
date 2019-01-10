<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\TransactionHashValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\TestCase;

class TransactionHashValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var TransactionHashValidator
     */
    private $validator;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new TransactionHashValidator(
            $this->resultFactoryMock,
            new SubjectReader(),
            $this->configMock
        );
    }

    public function testValidateFailsWhenNeitherMethodIsAvailable()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->validator->validate(['response' => []]);

        $this->assertFalse($args['isValid']);
        $this->assertEquals([TransactionHashValidator::ERROR_TRANSACTION_HASH], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateFailsWhenInvalidSha512HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->configMock->expects($this->once())
            ->method('getTransactionSignatureKey')
            ->willReturn('abc');

        $this->validator->validate([
            'amount' => '123.00',
            'response' => [
                'transactionResponse' => [
                    'transHashSHA2' => 'bad'
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertEquals([TransactionHashValidator::ERROR_TRANSACTION_HASH], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateSucceedsWhenValidSha512HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->configMock->expects($this->once())
            ->method('getTransactionSignatureKey')
            ->willReturn('abc');

        $this->validator->validate([
            'amount' => '123.00',
            'merchantAuthentication' => [
                'name' => 'username',
            ],
            'response' => [
                'transactionResponse' => [
                    'transId' => '123',
                    'transHashSHA2' => 'b4eb297d45976bc3bfc2f078cf026d075bf3942ec02aa94140a709f106'
                        . '772308cf36b7b364371ce389b687dc03ef93f58f3f4ba2ba5a2fde4cf23695ec9b8e43'
                ]
            ]
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['errorCodes']);
        $this->assertEmpty($args['failsDescription']);
    }

    public function testValidateFailsWhenInvalidMD5HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->configMock->expects($this->once())
            ->method('getLegacyTransactionHash')
            ->willReturn('abc');

        $this->validator->validate([
            'amount' => '123.00',
            'response' => [
                'transactionResponse' => [
                    'transHash' => 'bad'
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertEquals([TransactionHashValidator::ERROR_TRANSACTION_HASH], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateSucceedsWhenValidMd5HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->configMock->expects($this->once())
            ->method('getLegacyTransactionHash')
            ->willReturn('abc');

        $this->validator->validate([
            'amount' => '123.00',
            'merchantAuthentication' => [
                'name' => 'username',
            ],
            'response' => [
                'transactionResponse' => [
                    'transId' => '123',
                    'transHash' => '44BCA82F40E417C51E842630FDAAAB88'
                ]
            ]
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['errorCodes']);
        $this->assertEmpty($args['failsDescription']);
    }
}
