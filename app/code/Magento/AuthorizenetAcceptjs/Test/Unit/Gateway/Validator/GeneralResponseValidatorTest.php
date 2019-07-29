<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Gateway\Validator\GeneralResponseValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Validator\GeneralResponseValidator
 */
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->resultFactoryMock = $this->createMock(ResultInterfaceFactory::class);
        $this->validator = $objectManagerHelper->getObject(
            GeneralResponseValidator::class,
            [
                'resultInterfaceFactory' => $this->resultFactoryMock,
                'subjectReader' => new SubjectReader(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testValidateParsesSuccess()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->createMock(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Ok',
                    'message' => [
                        [
                            'code' => 'foo',
                            'text' => 'bar',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertTrue($args['isValid']);
        $this->assertEmpty($args['failsDescription']);
    }

    /**
     * @return void
     */
    public function testValidateParsesErrors()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->createMock(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'errors' => [
                    'resultCode' => 'Error',
                    'error' => [
                        [
                            'errorCode' => 'foo',
                            'errorText' => 'bar',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['failsDescription']);
    }

    /**
     * @return void
     */
    public function testValidateParsesMessages()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->createMock(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Error',
                    'message' => [
                        [
                            'code' => 'foo',
                            'text' => 'bar',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['failsDescription']);
    }

    /**
     * @return void
     */
    public function testValidateParsesErrorsWhenOnlyOneIsReturned()
    {
        $args = [];

        $this->resultFactoryMock->method('create')
            ->with($this->callback(function ($a) use (&$args) {
                // Spy on method call
                $args = $a;

                return true;
            }))
            ->willReturn($this->createMock(ResultInterface::class));

        $this->validator->validate([
            'response' => [
                'messages' => [
                    'resultCode' => 'Error',
                    'message' => [
                        'code' => 'foo',
                        'text' => 'bar',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($args['isValid']);
        $this->assertSame(['foo'], $args['failsDescription']);
    }
}
