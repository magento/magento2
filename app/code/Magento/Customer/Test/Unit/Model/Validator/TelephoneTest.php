<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Validator\Telephone;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\Pattern\TelephoneValidator;
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
    private Telephone $telephoneValidator;

    /**
     * @var TelephoneValidator|MockObject
     */
    private MockObject $telephoneValidatorMock;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->telephoneValidatorMock = $this->createMock(TelephoneValidator::class);
        $this->telephoneValidator = new Telephone($this->telephoneValidatorMock);
        $this->customerMock = $this
            ->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getTelephone', 'getFax'])
            ->getMock();
    }

    /**
     * Test for allowed punctuation characters in customer telephone numbers
     *
     * @param string $telephone
     * @param string $fax
     * @param string $message
     * @return void
     * @dataProvider expectedPunctuationInTelephoneDataProvider
     */
    public function testValidateCorrectPunctuationInTelephone(
        string $telephone,
        string $fax,
        string $message
    ): void {
        $this->customerMock->expects($this->once())->method('getTelephone')->willReturn($telephone);
        $this->customerMock->expects($this->once())->method('getFax')->willReturn($fax);

        $isValid = $this->telephoneValidator->isValid($this->customerMock);
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
                'fax' => '123456789',
                'message' => 'Parentheses must be allowed in telephone, and digits must be allowed in fax.'
            ],
            [
                'telephone' => '+6255554444',
                'fax' => '123 456 789',
                'message' => 'Plus sign must be allowed in telephone, and spaces must be allowed in fax.'
            ],
            [
                'telephone' => '555-555-555',
                'fax' => '123/456/789',
                'message' => 'Hyphen must be allowed in telephone, and forward slashes must be allowed in fax.'
            ]
        ];
    }
}
