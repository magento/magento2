<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

/**
 * Test for \Magento\Customer\Model\PasswordStrength
 */
class PasswordStrengthTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Stdlib\StringUtils */
    protected $stringHelper;

    /** @var \Magento\Customer\Helper\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerConfigHelperMock;

    /** @var \Magento\Customer\Model\PasswordStrength */
    protected $model;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->stringHelper = $objectManager->getObject('\Magento\Framework\Stdlib\StringUtils');

        $this->customerConfigHelperMock = $this->getMock(
            '\Magento\Customer\Helper\Config',
            [],
            [],
            '',
            false
        );

        $this->model = $objectManager->getObject(
            '\Magento\Customer\Model\PasswordStrength',
            [
                'stringHelper' => $this->stringHelper,
                'customerConfigHelper' => $this->customerConfigHelperMock
            ]
        );
    }

    /**
     * @param int $testNumber
     * @param string $password
     * @param int $minPasswordLength
     * @dataProvider dataProviderCheckPasswordStrength
     */
    public function testCheckPasswordStrength($testNumber, $password, $minPasswordLength)
    {
        $this->customerConfigHelperMock->expects($this->once())
            ->method('getMinimumPasswordLength')
            ->willReturn($minPasswordLength);

        if ($testNumber==1) {
            $this->setExpectedException(
                '\Magento\Framework\Exception\InputException',
                __('Please enter a password with at least %1 characters.', $minPasswordLength)
            );
        }

        if ($testNumber==2) {
            $this->setExpectedException(
                '\Magento\Framework\Exception\InputException',
                __('The password can\'t begin or end with a space.')
            );
        }

        if ($testNumber>=3) {
            $requiredCharactersCheck = 4;

            $this->customerConfigHelperMock->expects($this->once())
                ->method('getRequiredCharacterClassesNumber')
                ->willReturn($requiredCharactersCheck);

            if ($testNumber==3) {
                $this->setExpectedException(
                    '\Magento\Framework\Exception\InputException',
                    __(
                        'Minimum different classes of characters in password are %1.' .
                        ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                        $requiredCharactersCheck
                    )
                );
            }
        }

        $this->model->checkPasswordStrength($password);
    }

    /**
     * @return array
     */
    public function dataProviderCheckPasswordStrength()
    {
        return [
            [
                'testNumber' => 1,
                'password' => '',
                'minPasswordLength' => null
            ],
            [
                'testNumber' => 2,
                'password' => '1234567 ',
                'minPasswordLength' => 5
            ],
            [
                'testNumber' => 3,
                'password' => '1234567',
                'minPasswordLength' => 5
            ],
            [
                'testNumber' => 4,
                'password' => '123abC$',
                'minPasswordLength' => 5
            ]
        ];
    }

    /**
     * @param int $testNumber
     * @param string $password
     * @dataProvider dataProviderCheckLoginPasswordStrength
     */
    public function testCheckLoginPasswordStrength($testNumber, $password)
    {
        if ($testNumber==1) {
            $this->setExpectedException(
                '\Magento\Framework\Exception\InputException',
                __(
                    'Please enter a password with at least %1 characters.',
                    \Magento\Customer\Model\PasswordStrength::MIN_PASSWORD_LENGTH
                )
            );
        }

        if ($testNumber==2) {
            $this->setExpectedException(
                '\Magento\Framework\Exception\InputException',
                __('The password can\'t begin or end with a space.')
            );
        }

        $this->model->checkLoginPasswordStrength($password);
    }

    /**
     * @return array
     */
    public function dataProviderCheckLoginPasswordStrength()
    {
        return [
            [
                'testNumber' => 1,
                'password' => ''
            ],
            [
                'testNumber' => 2,
                'password' => '1234567 '
            ],
            [
                'testNumber' => 3,
                'password' => '1234567'
            ]
        ];
    }
}
