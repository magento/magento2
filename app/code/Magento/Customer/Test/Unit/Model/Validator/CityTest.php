<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address\Validator;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\City;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for validating city field in address.
 */
class CityTest extends TestCase
{
    /**
     * @var City
     */
    private City $cityValidator;

    /**
     * @var AbstractAddress|MockObject
     */
    private MockObject $addressMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cityValidator = new City();
        
        $this->addressMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCity'])
            ->getMock();        
    }

    /**
     * Test for valid city name
     *
     * @param string $city
     * @param bool $expectedIsValid
     * @return void
     * @dataProvider expectedPunctuationInCityDataProvider
     */
    public function testValidateCityName(
        string $city, 
        bool $expectedIsValid
    ): void {
        $this->addressMock->expects($this->once())->method('getCity')->willReturn($city);

        $isValid = $this->cityValidator->isValid($city);
        $this->assertTrue($isValid, $message);
    }

    /**
     * Data provider for city names
     *
     * @return array
     */
    public function expectedPunctuationInCityDataProvider(): array
    {
        return [
            [
                'city' => 'New York',
                'message' => 'Spaces must be allowed in city names'
            ],
            [
                'city' => 'São Paulo',
                'message' => 'Accented characters and spaces must be allowed in city names'
            ],
            [
                'city' => 'St. Louis',
                'message' => 'Periods and spaces must be allowed in city names'
            ],
            [
                'city' => 'Москва',
                'message' => 'Unicode letters must be allowed in city names'
            ],
            [
                'city' => 'Moscow \'',
                'message' => 'Apostrophe characters must be allowed in city names'
            ],
            [
                'city' => 'St.-Pierre',
                'message' => 'Hyphens must be allowed in city names'
            ],
            [
                'city' => 'Offenbach (Main)',
                'message' => 'Parentheses must be allowed in city names'
            ],
            [
                'city' => 'Rome: The Eternal City',
                'message' => 'Colons must be allowed in city names'
            ],
        ];
    }
}
