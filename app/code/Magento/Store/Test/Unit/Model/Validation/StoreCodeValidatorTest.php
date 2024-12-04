<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Validation;

use Magento\Framework\Validator\Regex;
use Magento\Framework\Validator\RegexFactory;
use Magento\Store\Model\Validation\StoreCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreCodeValidatorTest extends TestCase
{
    /**
     * @var RegexFactory|MockObject
     */
    private $regexValidatorFactoryMock;

    /**
     * @var Regex|MockObject
     */
    private $regexValidatorMock;

    /**
     * @var StoreCodeValidator
     */
    private $model;

    protected function setUp(): void
    {
        $this->regexValidatorFactoryMock = $this->getMockBuilder(RegexFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->regexValidatorMock = $this->createMock(Regex::class);
        $this->regexValidatorFactoryMock->method('create')
            ->willReturn($this->regexValidatorMock);

        $this->model = new StoreCodeValidator($this->regexValidatorFactoryMock);
    }

    /**
     * @dataProvider isValidDataProvider
     * @param string $value
     * @param bool $isValid
     * @param array $messages
     */
    public function testIsValid(string $value, bool $isValid, array $messages): void
    {
        $this->regexValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($value)
            ->willReturn($isValid);
        $this->regexValidatorMock->expects($this->once())
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
                'abc',
                true,
                []
            ],
            'false' => [
                '5',
                false,
                ['code is not valid']
            ],
        ];
    }
}
