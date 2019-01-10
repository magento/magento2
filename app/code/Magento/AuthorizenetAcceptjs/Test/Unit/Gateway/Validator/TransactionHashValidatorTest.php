<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\GeneralResponseValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\TestCase;

class GeneralResponseValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var GeneralResponseValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->resultFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new GeneralResponseValidator($this->resultFactoryMock, new SubjectReader());
    }

    public function testValidateParsesSuccess()
    {
        $args = [];

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => GeneralResponseValidator::RESULT_CODE_SUCCESS,
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

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => GeneralResponseValidator::RESULT_CODE_ERROR,
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

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => GeneralResponseValidator::RESULT_CODE_ERROR,
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
