<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\GeneralResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneralResponseValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var GeneralResponseValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->validator = new GeneralResponseValidator($this->resultFactoryMock, new SubjectReader());
    }

    public function testValidateParsesSuccess()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->getMockForAbstractClass(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Ok',
                    'message' => [
                        [
                            'code' => 'foo',
                            'text' => 'bar'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['errorCodes']);
        $this->assertEmpty($args['failsDescription']);
    }

    public function testValidateParsesErrors()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->getMockForAbstractClass(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'errors' => [
                    'resultCode' => 'Error',
                    'error' => [
                        [
                            'errorCode' => 'foo',
                            'errorText' => 'bar'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['errorCodes']);
        $this->assertSame(['bar'], $args['failsDescription']);
    }

    public function testValidateParsesMessages()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->getMockForAbstractClass(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Error',
                    'message' => [
                        [
                            'code' => 'foo',
                            'text' => 'bar'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['errorCodes']);
        $this->assertSame(['bar'], $args['failsDescription']);
    }

    public function testValidateParsesErrorsWhenOnlyOneIsReturned()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->getMockForAbstractClass(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Error',
                    'message' => [
                        'code' => 'foo',
                        'text' => 'bar'
                    ]
                ]
            ]
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['errorCodes']);
        $this->assertSame(['bar'], $args['failsDescription']);
    }
}
