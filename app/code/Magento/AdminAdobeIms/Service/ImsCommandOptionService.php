<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ImsCommandOptionService
{
    /**
     * Prompt for CLI command options
     */
    private const OPTION_QUESTION = 'Please enter your %s:';

    /**
     * Prompt for 2FA CLI Command option
     */
    private const TWO_FACTOR_OPTION_QUESTION = 'Is 2FA enabled for Organization in Adobe Admin Console? (yes/no):';

    /**
     * Human-readable name for Organization ID input option
     */
    private const ORGANIZATION_ID_NAME = 'Organization ID';

    /**
     * Human-readable name for Client ID input option
     */
    private const CLIENT_ID_NAME = 'Client ID';

    /**
     * Human-readable name for Client Secret input option
     */
    private const CLIENT_SECRET_NAME = 'Client Secret';

    /**
     * Human-readable name for 2FA Enabled input option
     */
    private const TWO_FACTOR_AUTH_NAME = '2FA Enabled';

    /**
     * @var ImsCommandValidationService
     */
    private ImsCommandValidationService $imsCommandValidationService;

    /**
     * @param ImsCommandValidationService $imsCommandValidationService
     */
    public function __construct(
        ImsCommandValidationService $imsCommandValidationService
    ) {
        $this->imsCommandValidationService = $imsCommandValidationService;
    }

    /**
     * Get Organization ID from option arguments or create prompt
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $helper
     * @param string $optionArgument
     * @return string
     * @throws LocalizedException
     */
    public function getOrganizationId(
        InputInterface $input,
        OutputInterface $output,
        $helper,
        string $optionArgument
    ): string {
        $organizationId = trim($input->getOption($optionArgument) ?? '');

        if (!$organizationId) {
            $question = $this->askForOrganizationId();
            $organizationId = $helper->ask($input, $output, $question);
        } else {
            $organizationId = $this->organizationIdValidation($organizationId);
        }

        return $organizationId;
    }

    /**
     * Get Client ID from option arguments or create prompt
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $helper
     * @param string $optionArgument
     * @return string
     * @throws LocalizedException
     */
    public function getClientId(
        InputInterface $input,
        OutputInterface $output,
        $helper,
        string $optionArgument
    ): string {
        $clientId = trim($input->getOption($optionArgument) ?? '');

        if (!$clientId) {
            $question = $this->askForClientId();
            $clientId = $helper->ask($input, $output, $question);
        } else {
            $clientId = $this->clientIdValidation($clientId);
        }

        return $clientId;
    }

    /**
     * Get Client Secret from option arguments or create prompt
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $helper
     * @param string $optionArgument
     * @return string
     * @throws LocalizedException
     */
    public function getClientSecret(
        InputInterface $input,
        OutputInterface $output,
        $helper,
        string $optionArgument
    ): string {
        $clientSecret = trim($input->getOption($optionArgument) ?? '');

        if (!$clientSecret) {
            $question = $this->askForClientSecret();
            $clientSecret = $helper->ask($input, $output, $question);
        } else {
            $clientSecret = $this->clientSecretValidation($clientSecret);
        }

        return $clientSecret;
    }

    /**
     * Get 2FA State from option arguments or create prompt
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param mixed $helper
     * @param string $optionArgument
     * @return bool
     * @throws LocalizedException
     */
    public function isTwoFactorAuthEnabled(
        InputInterface $input,
        OutputInterface $output,
        $helper,
        string $optionArgument
    ): bool {
        $twoFactorAuthEnabled = trim($input->getOption($optionArgument) ?? '');

        if (!$twoFactorAuthEnabled) {
            $question = $this->askForTwoFactorAuth();
            $twoFactorAuthEnabled = $helper->ask($input, $output, $question);
        } else {
            $twoFactorAuthEnabled = $this->twoFactorAuthValidation($twoFactorAuthEnabled);
        }

        return $twoFactorAuthEnabled;
    }

    /**
     * Prepare Question for parameter
     *
     * @param string $paramName
     * @return Question
     */
    private function prepareQuestion(string $paramName): Question
    {
        return new Question(
            sprintf(self::OPTION_QUESTION, $paramName),
            ''
        );
    }

    /**
     * Prepare Question for 2FA State
     *
     * @return ConfirmationQuestion
     */
    private function prepareQuestionForTwoFactorAuth(): ConfirmationQuestion
    {
        return new ConfirmationQuestion(
            self::TWO_FACTOR_OPTION_QUESTION,
            false
        );
    }

    /**
     * Prepare Question for organization id
     *
     * @return Question
     */
    private function askForOrganizationId(): Question
    {
        $question = $this->prepareQuestion(self::ORGANIZATION_ID_NAME);
        $question->setValidator(
            function ($value) {
                return $this->organizationIdValidation($value);
            }
        );

        return $question;
    }

    /**
     * Prepare Question for client id
     *
     * @return Question
     */
    private function askForClientId(): Question
    {
        $question = $this->prepareQuestion(self::CLIENT_ID_NAME);
        $question->setValidator(
            function ($value) {
                return $this->clientIdValidation($value);
            }
        );

        return $question;
    }

    /**
     * Prepare Hidden Question for client secret
     *
     * @return Question
     */
    private function askForClientSecret(): Question
    {
        $question = $this->prepareQuestion(self::CLIENT_SECRET_NAME);
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(
            function ($value) {
                return $this->clientSecretValidation($value);
            }
        );

        return $question;
    }

    /**
     * Prepare Question for 2FA state
     *
     * @return Question
     */
    private function askForTwoFactorAuth(): Question
    {
        return $this->prepareQuestionForTwoFactorAuth();
    }

    /**
     * Validation for organizationId
     *
     * @param string $organizationId
     * @return string
     * @throws LocalizedException
     */
    private function organizationIdValidation(string $organizationId): string
    {
        return $this->imsCommandValidationService->organizationIdValidator($organizationId);
    }

    /**
     * Validation for clientId
     *
     * @param string $clientId
     * @return string
     * @throws LocalizedException
     */
    private function clientIdValidation(string $clientId): string
    {
        return $this->imsCommandValidationService->clientIdValidator($clientId);
    }

    /**
     * Validation for clientSecret
     *
     * @param string $clientSecret
     * @return string
     * @throws LocalizedException
     */
    private function clientSecretValidation(string $clientSecret): string
    {
        return $this->imsCommandValidationService->clientSecretValidator($clientSecret);
    }

    /**
     * Validation for twoFactorAuth
     *
     * @param string $twoFactorAuthEnabled
     * @return bool
     * @throws LocalizedException
     */
    private function twoFactorAuthValidation(string $twoFactorAuthEnabled): bool
    {
        return $this->imsCommandValidationService->twoFactorAuthValidator($twoFactorAuthEnabled);
    }
}
