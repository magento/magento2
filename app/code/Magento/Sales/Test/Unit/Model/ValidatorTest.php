<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Validator;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\ValidatorResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Sales\Model\Validator
 */
class ValidatorTest extends TestCase
{

    /**
     * Testable Object
     *
     * @var Validator
     */
    private $validator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ValidatorResultInterfaceFactory|MockObject
     */
    private $validatorResultFactoryMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $validatorResultMock;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validatorMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $entityMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->entityMock = $this->createMock(OrderInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->validatorResultFactoryMock = $this->getMockBuilder(ValidatorResultInterfaceFactory::class)
            ->onlyMethods(['create'])->disableOriginalConstructor()
            ->getMock();
        $this->validatorResultMock = $this->createMock(ValidatorResultInterface::class);
        $this->validatorResultFactoryMock->expects($this->any())->method('create')
            ->willReturn($this->validatorResultMock);
        $this->objectManager = new ObjectManager($this);
        $this->validator = $this->objectManager->getObject(
            Validator::class,
            [
                'objectManager' => $this->objectManagerMock,
                'validatorResult' => $this->validatorResultFactoryMock,
            ]
        );
    }

    /**
     * Test validate method
     *
     * @return void
     *
     * @throws ConfigurationMismatchException
     */
    public function testValidate()
    {
        $validatorName = 'test';
        $validators = [$validatorName];
        $context = new DataObject();
        $validatorArguments = ['context' => $context];
        $message = __('Sample message.');
        $messages = [$message];

        $this->objectManagerMock->expects($this->once())->method('create')
            ->with($validatorName, $validatorArguments)->willReturn($this->validatorMock);
        $this->validatorMock->expects($this->once())->method('validate')->with($this->entityMock)
            ->willReturn($messages);
        $this->validatorResultMock->expects($this->once())->method('addMessage')->with($message);

        $expected = $this->validatorResultMock;
        $actual = $this->validator->validate($this->entityMock, $validators, $context);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test validate method
     *
     * @return void
     *
     * @throws ConfigurationMismatchException
     */
    public function testValidateWithException()
    {
        $validatorName = 'test';
        $validators = [$validatorName];
        $this->objectManagerMock->expects($this->once())->method('create')->willReturn(null);
        $this->validatorResultMock->expects($this->never())->method('addMessage');
        $this->expectException(ConfigurationMismatchException::class);
        $this->validator->validate($this->entityMock, $validators);
    }
}
