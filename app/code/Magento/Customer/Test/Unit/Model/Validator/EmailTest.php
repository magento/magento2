<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Validator\Email;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer email validator tests.
 */
class EmailTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Email
     */
    private Email $emailValidator;

    /**
     * @var Customer|MockObject
     */
    private MockObject $customerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->emailValidator = new Email();
        $this->customerMock = $this->createPartialMockWithReflection(
            Customer::class,
            ['getEmail']
        );
    }

    /**
     * @param string|null $email
     * @param bool $expectedIsValid
     * @return void
     */
    #[DataProvider('emailLengthDataProvider')]
    public function testValidateEmailLength(?string $email, bool $expectedIsValid): void
    {
        $this->customerMock->expects($this->once())->method('getEmail')->willReturn($email);

        $isValid = $this->emailValidator->isValid($this->customerMock);

        $this->assertSame($expectedIsValid, $isValid);
        if (!$expectedIsValid) {
            $this->assertSame(
                [['email' => '"Email" uses too many characters.']],
                $this->emailValidator->getMessages()
            );
        }
    }

    /**
     * @return array
     */
    public static function emailLengthDataProvider(): array
    {
        return [
            'email at max length is valid' => [
                'email' => str_repeat('a', 243) . '@example.com',
                'expectedIsValid' => true,
            ],
            'email exceeding max length is invalid' => [
                'email' => str_repeat('a', 244) . '@example.com',
                'expectedIsValid' => false,
            ],
            'null email is valid' => [
                'email' => null,
                'expectedIsValid' => true,
            ],
        ];
    }
}
