<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\City;
use Magento\Customer\Model\Customer;
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
            ]
        ];
    }
}
