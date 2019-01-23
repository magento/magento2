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
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\TestCase;

class TransactionHashValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultFactoryMock;

    /**
     * @var TransactionHashValidator
     */
    private $validator;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var ResultInterface
     */
    private $resultMock;

    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
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

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->validator->validate(['response' => []]);

        $this->assertFalse($args['isValid']);
        $this->assertEquals(['ETHV'], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateFailsWhenInvalidSha512HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getTransactionSignatureKey')
            ->willReturn('abc');

        $this->validator->validate([
            'amount' => '123.00',
            'response' => [
                'transactionResponse' => [
                    'transHashSha2' => 'bad'
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertEquals(['ETHV'], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateSucceedsWhenValidSha512HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getTransactionSignatureKey')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate([
            'amount' => '123.00',
            'response' => [
                'transactionResponse' => [
                    'transId' => '123',
                    'transHashSha2' => '1DBD16DED0DA02F52A22A9AD71A49F70BD2ECD42437552889912DD5CE'
                        . 'CBA0E09A5E8E6221DA74D98A46E5F77F7774B6D9C39CADF3E9A33D85870A6958DA7C8B2'
                ]
            ]
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['errorCodes']);
        $this->assertEmpty($args['failsDescription']);
    }

    public function testValidateSucceedsWhenValidSha512HashIsReceivedWithNoAmount()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getTransactionSignatureKey')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate([
            'response' => [
                'transactionResponse' => [
                    'transId' => '123',
                    'transHashSha2' => 'CC0FF465A081D98FFC6E502C40B2DCC7655ACF591F859135B6E66558D4'
                        . '1E3A2C654D5A2ACF4749104F3133711175C232C32676F79F70211C2984B21A33D30DEE'
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

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getLegacyTransactionHash')
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
        $this->assertEquals(['ETHV'], $args['errorCodes']);
        $this->assertEquals(
            ['The authenticity of the gateway response could not be verified.'],
            $args['failsDescription']
        );
    }

    public function testValidateSucceedsWhenValidMd5HashIsReceived()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getLegacyTransactionHash')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate([
            'amount' => '123.00',
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

    public function testValidateSucceedsWhenValidMd5HashIsReceivedWithNoAmount()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->resultMock);

        $this->configMock->method('getLegacyTransactionHash')
            ->willReturn('abc');
        $this->configMock->method('getLoginId')
            ->willReturn('username');

        $this->validator->validate([
            'response' => [
                'transactionResponse' => [
                    'transId' => '123',
                    'transHash' => 'C8675D9F7BE7BE4A04C18EA1B6F7B6FD'
                ]
            ]
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['errorCodes']);
        $this->assertEmpty($args['failsDescription']);
    }
}
