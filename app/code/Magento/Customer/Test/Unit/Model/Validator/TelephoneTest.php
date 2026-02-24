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
                'telephone' => '123.456.7890',
                'message' => 'Dots should not be allowed in telephone'
            ],
            [
                'telephone' => '123456789012345678901',
                'message' => 'Telephone number longer than 20 characters should not be allowed'
            ],
            [
                'telephone' => '<' . 'script>alert("xss")<' . '/script>',
                'message' => 'XSS attempt should not be allowed in telephone'
            ]
        ];
    }
}
