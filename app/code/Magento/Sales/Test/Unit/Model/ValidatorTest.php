<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * @covers \Magento\Sales\Model\Validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var Validator
     */
    private $validator;

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var ValidatorResultInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validatorResultFactoryMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validatorResultMock;

    /**
     * @var ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validatorMock;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $entityMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->entityMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $this->validatorResultFactoryMock = $this->getMockBuilder(ValidatorResultInterfaceFactory::class)
            ->setMethods(['create'])->disableOriginalConstructor()->getMock();
        $this->validatorResultMock = $this->getMockForAbstractClass(ValidatorResultInterface::class);
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
