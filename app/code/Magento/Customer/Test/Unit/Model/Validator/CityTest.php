<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\City;
use Magento\Customer\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer city validator tests
 */
class CityTest extends TestCase
{
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
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCity'])
            ->getMock();
    }

    /**
     * Test for allowed apostrophe and other punctuation characters in customer names
     *
     * @param string $city
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInNamesDataProvider
     */
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
            ]
        ];
    }
}
