<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Validation;

use Magento\Framework\Validator\DataObject;
use Magento\Framework\Validator\DataObjectFactory;
use Magento\Framework\Validator\ValidatorInterface;
use Magento\Store\Model\Validation\StoreValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreValidatorTest extends TestCase
{
    /**
     * @var DataObjectFactory|MockObject
     */
    private $dataObjectValidatorFactoryMock;

    /**
     * @var DataObject|MockObject
     */
    private $dataObjectValidatorMock;

    /**
     * @var array
     */
    private $ruleMocks;

    /**
     * @var StoreValidator
     */
    private $model;

    protected function setUp(): void
    {
        $this->dataObjectValidatorFactoryMock = $this->getMockBuilder(DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataObjectValidatorMock = $this->createMock(DataObject::class);
        $this->dataObjectValidatorFactoryMock->method('create')
            ->willReturn($this->dataObjectValidatorMock);
        $ruleMock1 = $this->createMock(ValidatorInterface::class);
        $ruleMock2 = $this->createMock(ValidatorInterface::class);
        $this->ruleMocks = [
            [$ruleMock1, 'field1'],
            [$ruleMock2, 'field2'],
        ];

        $this->model = new StoreValidator(
            $this->dataObjectValidatorFactoryMock,
            array_combine(array_column($this->ruleMocks, 1), array_column($this->ruleMocks, 0))
        );
    }

    /**
     * @dataProvider isValidDataProvider
     * @param \Magento\Framework\DataObject $value
     * @param bool $isValid
     * @param array $messages
     */
    public function testIsValid(\Magento\Framework\DataObject $value, bool $isValid, array $messages): void
    {
        $this->dataObjectValidatorMock->expects($this->exactly(count($this->ruleMocks)))
            ->method('addRule')
            ->withConsecutive(...$this->ruleMocks);
        $this->dataObjectValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($value)
            ->willReturn($isValid);
        $this->dataObjectValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $result = $this->model->isValid($value);
        $this->assertEquals($isValid, $result);
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public function isValidDataProvider(): array
    {
        return [
            'true' => [
                new \Magento\Framework\DataObject(['field1' => 'value1', 'field2' => 'value2']),
                true,
                []
            ],
            'false' => [
                new \Magento\Framework\DataObject(),
                false,
                ['store is not valid']
            ],
        ];
    }
}
