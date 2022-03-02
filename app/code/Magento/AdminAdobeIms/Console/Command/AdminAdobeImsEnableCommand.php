<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Console\Command;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command to set Admin Adobe IMS Module mode
 */
class AdminAdobeImsEnableCommand extends Command
{
    /**
     * Name of "organization-id" input option
     */
    private const ORGANIZATION_ID_ARGUMENT = 'organization-id';

    /**
     * Name of "client-id" input option
     */
    private const CLIENT_ID_ARGUMENT = 'client-id';

    /**
     * Name of "client-secret" input option
     */
    private const CLIENT_SECRET_ARGUMENT = 'client-secret';

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
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @param ImsConfig $imsConfig
     * @param ImsConnection $imsConnection
     */
    public function __construct(
        ImsConfig $imsConfig,
        ImsConnection $imsConnection
    ) {
        parent::__construct();
        $this->imsConfig = $imsConfig;
        $this->imsConnection = $imsConnection;

        $this->setName('admin:adobe-ims:enable')
            ->setDescription('Enable Adobe IMS Module.')
            ->setDefinition([
                new InputOption(
                    self::ORGANIZATION_ID_ARGUMENT,
                    'o',
                    InputOption::VALUE_OPTIONAL,
                    'Set Organization ID for Adobe IMS configuration. Required when enabling the module'
                ),
                new InputOption(
                    self::CLIENT_ID_ARGUMENT,
                    'c',
                    InputOption::VALUE_OPTIONAL,
                    'Set the client ID for Adobe IMS configuration. Required when enabling the module'
                ),
                new InputOption(
                    self::CLIENT_SECRET_ARGUMENT,
                    's',
                    InputOption::VALUE_OPTIONAL,
                    'Set the client Secret for Adobe IMS configuration. Required when enabling the module'
                )
            ]);

    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $organizationId = trim($input->getOption(self::ORGANIZATION_ID_ARGUMENT) ?? '');
            $clientId = trim($input->getOption(self::CLIENT_ID_ARGUMENT) ?? '');
            $clientSecret = trim($input->getOption(self::CLIENT_SECRET_ARGUMENT) ?? '');
            $helper = $this->getHelper('question');

            if (!$organizationId) {
                $question = $this->prepareQuestion(self::ORGANIZATION_ID_NAME);
                $organizationId = $helper->ask($input, $output, $question);
            }
            if (!$clientId) {
                $question = $this->prepareQuestion(self::CLIENT_ID_NAME);
                $clientId = $helper->ask($input, $output, $question);
            }

            if (!$clientSecret) {
                $question = $this->prepareHiddenQuestion(self::CLIENT_SECRET_NAME);
                $clientSecret = $helper->ask($input, $output, $question);
            }

            if ($clientId && $clientSecret && $organizationId) {
                $enabled = $this->enableModule($clientId, $clientSecret, $organizationId);
                if ($enabled) {
                    $output->writeln(__('Admin Adobe IMS integration is enabled'));
                    return Cli::RETURN_SUCCESS;
                }
            }

            throw new LocalizedException(
                __('The Client ID, Client Secret and Organization ID are required ' .
                    'when enabling the Admin Adobe IMS Module')
            );
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            // we must have an exit code higher than zero to indicate something was wrong
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * Prepare Question for parameter
     *
     * @param string $paramName
     * @return Question
     */
    private function prepareQuestion(string $paramName): Question
    {
        $question = new Question('Please enter your ' . $paramName . ':', '');
        $question->setValidator(function ($value) {
            if (trim($value) === '') {
                throw new LocalizedException(
                    __('This field is required to enable the Admin Adobe IMS Module')
                );
            }
            return $value;
        });

        return $question;
    }

    /**
     * Prepare Hidden Question for parameter
     *
     * @param string $paramName
     * @return Question
     */
    private function prepareHiddenQuestion(string $paramName): Question
    {
        $question = $this->prepareQuestion($paramName);
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return $question;
    }

    /**
     * Enable Admin Adobe IMS Module when testConnection was successfully
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $organizationId
     * @return bool
     * @throws InvalidArgumentException
     */
    private function enableModule(
        string $clientId,
        string $clientSecret,
        string $organizationId
    ): bool
    {
        $testAuth = $this->imsConnection->testAuth($clientId);
        if ($testAuth) {
            $this->imsConfig->enableModule($clientId, $clientSecret, $organizationId);
            return true;
        }

        return false;
    }
}
