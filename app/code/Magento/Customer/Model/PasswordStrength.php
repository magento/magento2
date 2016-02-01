<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Helper\Config as CustomerConfigHelper;
use Magento\Customer\Api\PasswordStrengthInterface;

/**
 * Class PasswordStrength. Works with strength of password
 *
 * @package Magento\Customer\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PasswordStrength implements PasswordStrengthInterface
{
    /**
     * Minimum password length
     */
    const MIN_PASSWORD_LENGTH = 1;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var CustomerConfigHelper
     */
    protected $customerConfigHelper;

    /**
     * @param StringHelper $stringHelper
     * @param CustomerConfigHelper $customerConfigHelper
     */
    public function __construct(
        StringHelper $stringHelper,
        CustomerConfigHelper $customerConfigHelper
    ) {
        $this->stringHelper = $stringHelper;
        $this->customerConfigHelper = $customerConfigHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPasswordStrength($password)
    {
        $configMinPasswordLength = $this->getMinPasswordLength();
        $length = $this->stringHelper->strlen($password);
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'Please enter a password with at least %1 characters.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(__('The password can\'t begin or end with a space.'));
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum different classes of characters in password are %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->customerConfigHelper->getRequiredCharacterClassesNumber();
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter ++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter ++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function checkLoginPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length < self::MIN_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at least %1 characters.',
                    self::MIN_PASSWORD_LENGTH
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(__('The password can\'t begin or end with a space.'));
        }
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength()
    {
        $minPasswordLength = $this->customerConfigHelper->getMinimumPasswordLength();
        if ($minPasswordLength === null) {
            $minPasswordLength = self::MIN_PASSWORD_LENGTH;
        }

        return $minPasswordLength;
    }
}
