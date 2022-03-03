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
use Symfony\Component\Console\Question\Question;

class ImsCommandOptionService
{
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
     * @param $helper
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
            $this->organizationIdValidation($organizationId);
        }

        return $organizationId;
    }

    /**
     * Get Client ID from option arguments or create prompt
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $helper
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
            $this->clientIdValidation($clientId);
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
            $this->clientSecretValidation($clientSecret);
        }

        return $clientSecret;
    }

    /**
     * Prepare Question for parameter
     *
     * @param string $paramName
     * @return Question
     */
    private function prepareQuestion(string $paramName): Question
    {
        return new Question('Please enter your ' . $paramName . ':', '');
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
                $this->organizationIdValidation($value);
                return $value;
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
                $this->clientIdValidation($value);
                return $value;
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
                $this->clientSecretValidation($value);
                return $value;
            }
        );

        return $question;
    }

    /**
     * Validation for organizationId
     *
     * @param string $organizationId
     * @throws LocalizedException
     */
    private function organizationIdValidation(string $organizationId): void
    {
        $this->imsCommandValidationService->emptyValueValidator($organizationId);
        $this->imsCommandValidationService->organizationIdValidator($organizationId);
    }

    /**
     * Validation for clientId
     *
     * @param string $clientId
     * @throws LocalizedException
     */
    private function clientIdValidation(string $clientId): void
    {
        $this->imsCommandValidationService->emptyValueValidator($clientId);
        $this->imsCommandValidationService->clientIdValidator($clientId);
    }

    /**
     * Validation for clientSecret
     *
     * @param string $clientSecret
     * @throws LocalizedException
     */
    private function clientSecretValidation(string $clientSecret): void
    {
        $this->imsCommandValidationService->emptyValueValidator($clientSecret);
        $this->imsCommandValidationService->clientSecretValidator($clientSecret);
    }
}
