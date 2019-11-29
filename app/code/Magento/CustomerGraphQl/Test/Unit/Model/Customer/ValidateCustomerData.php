<?php

namespace Magento\CustomerGraphQl\Test\Unit\Model\Customer;

use Magento\CustomerGraphQl\Model\Customer\GetAllowedCustomerAttributes;
use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerData as ValidateCustomerDataClass;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

class ValidateCustomerData extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider validEmailAddressDataProvider
     *
     * @param string $email
     *
     * @return void
     *
     * @throws GraphQlInputException
     */
    public function testExecuteWithValidEmailAddresses($email)
    {
        $getAllowedCustomerAttributes = $this->getMockBuilder(GetAllowedCustomerAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $getAllowedCustomerAttributes
            ->method('execute')
            ->willReturn([]);

        $emailAddressValidator = new EmailAddressValidator();
        /** @var ValidateCustomerDataClass $validateCustomerData */
        $validateCustomerData = new ValidateCustomerDataClass($getAllowedCustomerAttributes, $emailAddressValidator);

        $validateCustomerData->execute(['email' => $email]);
        $this->assertTrue(true);
    }
    /**
     * @dataProvider invalidEmailAddressDataProvider
     *
     * @param string $email
     *
     * @return void
     *
     * @throws GraphQlInputException
     */
    public function testExecuteWithInvalidEmailAddresses($email)
    {
        $getAllowedCustomerAttributes = $this->getMockBuilder(GetAllowedCustomerAttributes::class)
            ->disableOriginalConstructor()
            ->getMock();
        $getAllowedCustomerAttributes
            ->method('execute')
            ->willReturn([]);

        $emailAddressValidator = new EmailAddressValidator();
        /** @var ValidateCustomerDataClass $validateCustomerData */
        $validateCustomerData = new ValidateCustomerDataClass($getAllowedCustomerAttributes, $emailAddressValidator);

        $this->expectExceptionMessage("\"{$email}\" is not a valid email address.");

        $validateCustomerData->execute(['email' => $email]);
    }

    /**
     * @return array
     */
    public function validEmailAddressDataProvider(): array
    {
        return [
            ['jdoe@site.com'],
            ['email@example.com'],
            ['firstname.lastname@example.com'],
            ['email@subdomain.example.com'],
            ['firstname+lastname@example.com'],
            ['1234567890@example.com'],
            ['email@example-one.com'],
            ['_______@example.com'],
            ['email@example.name'],
            ['email@example.museum'],
            ['email@example.co.jp'],
            ['firstname-lastname@example.com'],
        ];
    }

    /**
     * @return array
     */
    public function invalidEmailAddressDataProvider(): array
    {
        return [
            ['t@rioe.d5'],
            ['plainaddress'],
            ['jØrgen@somedomain.com'],
            ['#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            ['email.example.com'],
            ['email@example@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example'],
            ['email@111.222.333.44444'],
            ['“email”@example.com'],
        ];
    }
}
