<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config;

use Magento\Framework\MessageQueue\Publisher\Config\CompositeValidator;
use Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeValidatorTest extends TestCase
{
    /**
     * @var CompositeValidator
     */
    private $model;

    /**
     * @var MockObject
     */
    private $validatorOneMock;

    /**
     * @var MockObject
     */
    private $validatorTwoMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->validatorOneMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->validatorTwoMock = $this->getMockForAbstractClass(ValidatorInterface::class);

        $this->model = new CompositeValidator([$this->validatorOneMock, $this->validatorTwoMock]);
    }

    public function testValidate()
    {
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $this->validatorOneMock->expects($this->once())->method('validate')->with($expectedValidationData);
        $this->validatorTwoMock->expects($this->once())->method('validate')->with($expectedValidationData);
        $this->model->validate($expectedValidationData);
    }

    public function testValidatorThrowsException()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('test');
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $this->validatorOneMock
            ->expects($this->once())
            ->method('validate')
            ->willThrowException(new \LogicException('test'));
        $this->validatorTwoMock->expects($this->never())->method('validate');
        $this->model->validate($expectedValidationData);
    }

    public function testInvalidReaderInstance()
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(
            'Validator [stdClass] does not implements ' .
            'Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface'
        );
        $validator = new \stdClass();
        $model = new CompositeValidator([$validator]);
        $expectedValidationData = include __DIR__ . '/../../_files/queue_publisher/data_to_validate.php';
        $model->validate($expectedValidationData);
    }
}
