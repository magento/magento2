<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Telephone;
use Magento\Customer\Model\Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer telephone validator tests
 */
class TelephoneTest extends TestCase
{
    /**
     * @var Telephone
     */
    private Telephone $nameValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->nameValidator = new Telephone;
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getTelephone'])
            ->getMock();
    }

    /**
     * Test for allowed apostrophe and other punctuation characters in customer names
     *
     * @param string $telephone
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInNamesDataProvider
     */
    public function testValidateCorrectPunctuationInNames(
        string $telephone,
        string $message
    ) {
        $this->customerMock->expects($this->once())->method('getTelephone')->willReturn($telephone);

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
            ]
        ];
    }
}
