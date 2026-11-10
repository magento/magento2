<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\City;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Customer city validator tests
 */
class CityTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Maximum allowed city length.
     */
    private const MAX_CITY_LENGTH = 255;

    /**
     * @var City
     */
    private City $nameValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameValidator = new City;
        $this->customerMock = $this->createPartialMockWithReflection(
            Customer::class,
            ['getCity']
        );
    }

    /**
     * Test for allowed apostrophe and other punctuation characters in customer names
     *
     * @param string $city
     * @param string $message
     * @return void */
    #[DataProvider('expectedPunctuationInNamesDataProvider')]
    public function testValidateCorrectPunctuationInNames(
        string $city,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getCity')->willReturn($city);

        $isValid = $this->nameValidator->isValid($this->customerMock);
        $this->assertTrue($isValid, $message);
    }

    /**
     * @return array
     */
    public static function expectedPunctuationInNamesDataProvider(): array
    {
        return [
            [
                'city' => 'Москва',
                'message' => 'Unicode letters must be allowed in city'
            ],
            [
                'city' => 'Мо́сква',
                'message' => 'Unicode marks must be allowed in city'
            ],
            [
                'city' => ' Moscow \'',
                'message' => 'Apostrophe characters must be allowed in city'
            ],
            [
                'city' => ' Moscow Moscow',
                'message' => 'Whitespace characters must be allowed in city'
            ],
            [
                'city' => 'O\'Higgins',
                'message' => 'Straight apostrophe must be allowed in city names'
            ],
            [
                'city' => 'O’Higgins',
                'message' => 'Typographical apostrophe must be allowed in city names'
            ],
            [
                'city' => 'Saint_Petersburg',
                'message' => 'Underscore must be allowed in city names'
            ],
            [
                'city' => 'Stratford-upon-Avon',
                'message' => 'Hyphens must be allowed in city names'
            ],
            [
                'city' => 'St. Petersburg',
                'message' => 'Periods must be allowed in city names'
            ],
            [
                'city' => 'Trinidad & Tobago',
                'message' => 'Ampersand must be allowed in city names'
            ],
            [
                'city' => 'Winston-Salem (NC)',
                'message' => 'Parentheses must be allowed in city names'
            ],
            [
                'city' => 'Rostov-on-Don, Russia',
                'message' => 'Commas must be allowed in city names'
            ],
            [
                'city' => 'Zürich',
                'message' => 'Diacritic ö must be allowed in city names'
            ],
            [
                'city' => 'Niño',
                'message' => 'Diacritic ñ must be allowed in city names'
            ],
            [
                'city' => 'Montréal',
                'message' => 'Diacritic é must be allowed in city names'
            ],
            [
                'city' => 'Curaçao',
                'message' => 'Diacritic ç (cedilla) must be allowed in city names'
            ]
        ];
    }

    /**
     * Test that invalid character in city name causes validation to fail
     *
     * @param string $rejectedChar The rejected character
     * @param string $message Description for assertion message
     * @return void
     */
    #[DataProvider('rejectedCharacterDataProvider')]
    public function testValidateRejectsInvalidCharacter(string $rejectedChar, string $message): void
    {
        $this->customerMock->expects($this->once())->method('getCity')
            ->willReturn('ValidCity' . $rejectedChar . 'Name');

        $isValid = $this->nameValidator->isValid($this->customerMock);

        $this->assertFalse($isValid, $message);
        $messages = $this->nameValidator->getMessages();
        $this->assertNotEmpty($messages);
        $this->assertStringContainsString('Invalid City', (string)(reset($messages)['city'] ?? ''));
    }

    /**
     * @return array
     */
    public static function rejectedCharacterDataProvider(): array
    {
        return [
            [
                'rejectedChar' => '!',
                'message' => 'Exclamation mark must be rejected'
            ],
            [
                'rejectedChar' => '"',
                'message' => 'Double quote must be rejected'
            ],
            [
                'rejectedChar' => '#',
                'message' => 'Hash must be rejected'
            ],
            [
                'rejectedChar' => '?',
                'message' => 'Question mark must be rejected'
            ],
            [
                'rejectedChar' => '/',
                'message' => 'Forward slash must be rejected'
            ],
        ];
    }

    public function testCityUpTo255CharactersIsValid(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getCity']);
        $addressMock->expects($this->once())
            ->method('getCity')
            ->willReturn(str_repeat('A', self::MAX_CITY_LENGTH));

        $this->assertTrue($this->nameValidator->isValid($addressMock));
    }

    public function testCityExceeding255CharactersIsRejected(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getCity']);
        $addressMock->expects($this->once())
            ->method('getCity')
            ->willReturn(str_repeat('A', self::MAX_CITY_LENGTH + 1));

        $isValid = $this->nameValidator->isValid($addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'city name is too long',
            (string)($messages[0]['city'] ?? '')
        );
    }

    public function testCityInvalidCharsProduceCharsetErrorMessage(): void
    {
        $addressMock = $this->createPartialMockWithReflection(Address::class, ['getCity']);
        $addressMock->expects($this->once())
            ->method('getCity')
            ->willReturn('City!Name');

        $isValid = $this->nameValidator->isValid($addressMock);

        $this->assertFalse($isValid);
        $messages = $this->nameValidator->getMessages();
        $this->assertStringContainsString(
            'Invalid City',
            (string)($messages[0]['city'] ?? '')
        );
        $this->assertStringNotContainsString(
            'too long',
            (string)($messages[0]['city'] ?? '')
        );
    }
}
