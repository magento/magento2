<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Telephone;
use Magento\Customer\Model\Address;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Customer telephone validator tests
 */
class TelephoneTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Maximum allowed telephone length.
     */
    private const MAX_TELEPHONE_LENGTH = 255;

    /**
     * @var Telephone
     */
    private Telephone $nameValidator;

    /**
     * @var Address|MockObject
     */
    private MockObject $addressMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameValidator = new Telephone;
        $this->addressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getTelephone']
        );
    }

    /**
     * Test for allowed apostrophe and other punctuation characters in customer names
     *
     * @param string $telephone
     * @param string $message
     * @return void */
    #[DataProvider('expectedPunctuationInNamesDataProvider')]
    public function testValidateCorrectPunctuationInNames(
        string $telephone,
        string $message
    ) {
        $this->addressMock->expects($this->once())->method('getTelephone')->willReturn($telephone);

        $isValid = $this->nameValidator->isValid($this->addressMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * Test for invalid characters in telephone numbers
     *
     * @param string $telephone
     * @param string $message
     * @return void
     */
    #[DataProvider('invalidTelephoneDataProvider')]
    public function testValidateInvalidTelephone(
        string $telephone,
        string $message
    ) {
        $this->addressMock->expects($this->once())->method('getTelephone')->willReturn($telephone);

        $isValid = $this->nameValidator->isValid($this->addressMock);
        $this->assertFalse($isValid, $message);
    }

    /**
     * @return array
     */
    public static function expectedPunctuationInNamesDataProvider(): array
    {
        return [
            [
                'telephone' => '(1)99887766',
                'message' => 'parentheses must be allowed in telephone'
            ],
            [
                'telephone' => '+6255554444',
                'message' => 'plus sign be allowed in telephone'
            ],
            [
                'telephone' => '555-555-555',
                'message' => 'hyphen must be allowed in telephone'
            ],
            [
                'telephone' => '123456789',
                'message' => 'Digits (numbers) must be allowed in telephone'
            ],
            [
                'telephone' => '(123) 456-7890',
                'message' => 'spaces must be allowed in telephone'
            ],
            [
                'telephone' => '06.76.40.32.22',
                'message' => 'dots should be allowed in telephone (e.g. 06.76.40.32.22)'
            ],
            [
                'telephone' => '+43680/2149568',
                'message' => 'slash should be allowed in telephone (e.g. +43680/2149568)'
            ],
            [
                'telephone' => '1' . str_repeat('2', self::MAX_TELEPHONE_LENGTH - 1),
                'message' => 'Telephone number up to 255 characters should be allowed'
            ]
        ];
    }

    /**
     * Data provider for invalid telephone numbers
     *
     * @return array
     */
    public static function invalidTelephoneDataProvider(): array
    {
        return [
            [
                'telephone' => '123абв456',
                'message' => 'Cyrillic characters should not be allowed in telephone'
            ],
            [
                'telephone' => '123abc456',
                'message' => 'Latin letters should not be allowed in telephone'
            ],
            [
                'telephone' => 'aaaaaa',
                'message' => 'Pure alphabetic string should not be allowed in telephone'
            ],
            [
                'telephone' => '123@456',
                'message' => 'Special character @ should not be allowed in telephone'
            ],
            [
                'telephone' => '123#456',
                'message' => 'Special character # should not be allowed in telephone'
            ],
            [
                'telephone' => '123$456',
                'message' => 'Special character $ should not be allowed in telephone'
            ],
            [
                'telephone' => '1' . str_repeat('2', self::MAX_TELEPHONE_LENGTH),
                'message' => 'Telephone number longer than 255 characters should not be allowed'
            ],
            [
                'telephone' => '<' . 'script>alert("xss")<' . '/script>',
                'message' => 'XSS attempt should not be allowed in telephone'
            ]
        ];
    }

    public function testLongTelephoneProducesLengthErrorMessage(): void
    {
        $this->addressMock->expects($this->once())
            ->method('getTelephone')
            ->willReturn('1' . str_repeat('2', self::MAX_TELEPHONE_LENGTH));

        $isValid = $this->nameValidator->isValid($this->addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'phone number is too long',
            (string)($messages[0]['telephone'] ?? '')
        );
    }

    public function testInvalidCharsProduceCharsetErrorMessage(): void
    {
        $this->addressMock->expects($this->once())
            ->method('getTelephone')
            ->willReturn('123@456');

        $isValid = $this->nameValidator->isValid($this->addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'Please use 0-9, +, -, (, ), ., / and space',
            (string)($messages[0]['telephone'] ?? '')
        );
    }
}
