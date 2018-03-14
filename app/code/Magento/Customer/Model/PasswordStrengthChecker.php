<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Model\PasswordStrengthChecker\ResultInterface;
use Magento\Customer\Model\PasswordStrengthChecker\ResultInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\StringUtils as StringHelper;

/**
 * Class PasswordStrengthChecker
 * @package Magento\Customer\Model
 */
class PasswordStrengthChecker implements PasswordStrengthCheckerInterface
{
    /**
     * Configuration path to customer password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'customer/password/minimum_password_length';

    /**
     * Configuration path to customer password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'customer/password/required_character_classes_number';

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * PasswordStrengthChecker constructor.
     * @param StringHelper $stringHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        StringHelper $stringHelper,
        ScopeConfigInterface $scopeConfig,
        ResultInterfaceFactory $resultFactory
    ) {
        $this->stringHelper = $stringHelper;
        $this->scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Check password strength.
     *
     * @param string $password
     * @return ResultInterface
     */
    public function check(string $password): ResultInterface
    {
        /** @var ResultInterface $result */
        $result = $this->resultFactory->create(['valid' => true, 'message' => __('')]);
        $length = $this->stringHelper->strlen($password);

        if ($length > self::MAX_PASSWORD_LENGTH) {
            $result->setIsValid(false);
            $result->setMessage(__('Please enter a password with at most %1 characters.', self::MAX_PASSWORD_LENGTH));
        }

        $minPasswordLength = $this->getMinPasswordLength();
        if ($length < $minPasswordLength) {
            $result->setIsValid(false);
            $result->setMessage(__('Please enter a password with at least %1 characters.', $minPasswordLength));
        }

        if ($this->stringHelper->strlen(trim($password)) != $length) {
            $result->setIsValid(false);
            $result->setMessage(__('The password can\'t begin or end with a space.'));
        }

        $requiredCharactersCheck = $this->checkRequiredCharacters($password);
        if ($requiredCharactersCheck !== 0) {
            $result->setIsValid(false);
            $result->setMessage(__(
                'Minimum of different classes of characters in password is %1.' .
                ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                $requiredCharactersCheck
            ));
        }

        return $result;
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * Check required characters.
     *
     * @param string $password
     * @return int
     */
    private function checkRequiredCharacters(string $password): int
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }
}