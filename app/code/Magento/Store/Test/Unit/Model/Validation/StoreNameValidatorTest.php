<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Validation;

use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\NotEmptyFactory;
use Magento\Store\Model\Validation\StoreNameValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreNameValidatorTest extends TestCase
{
    /**
     * @var NotEmptyFactory|MockObject
     */
    private $notEmptyValidatorFactoryMock;

    /**
     * @var NotEmpty|MockObject
     */
    private $notEmptyValidatorMock;

    /**
     * @var StoreNameValidator
     */
    private $model;

    protected function setUp(): void
    {
        $this->notEmptyValidatorFactoryMock = $this->getMockBuilder(NotEmptyFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->notEmptyValidatorMock = $this->createMock(NotEmpty::class);
        $this->notEmptyValidatorFactoryMock->method('create')
            ->willReturn($this->notEmptyValidatorMock);

        $this->model = new StoreNameValidator($this->notEmptyValidatorFactoryMock);
    }

    /**
     * @dataProvider isValidDataProvider
     * @param string $value
     * @param bool $isValid
     * @param array $messages
     */
    public function testIsValid(string $value, bool $isValid, array $messages): void
    {
        $this->notEmptyValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($value)
            ->willReturn($isValid);
        $this->notEmptyValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messages);

        $result = $this->model->isValid($value);
        $this->assertEquals($isValid, $result);
        $this->assertEquals($messages, $this->model->getMessages());
    }

    public static function isValidDataProvider(): array
    {
        return [
            'true' => [
                'Name1',
                true,
                []
            ],
            'false' => [
                '',
                false,
                ['name is not valid']
            ],
        ];
    }
}
