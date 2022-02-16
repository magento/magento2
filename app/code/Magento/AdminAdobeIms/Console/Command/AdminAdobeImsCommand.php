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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command to set Admin Adobe IMS Module mode
 */
class AdminAdobeImsCommand extends Command
{
    private const MODE_ENABLE = 'enable';
    private const MODE_DISABLE = 'disable';
    private const MODE_STATUS = 'status';

    /**
     * Name of "target application mode" input argument
     */
    private const MODE_ARGUMENT = 'status';

    /**
     * Name of "client-id" input option
     */
    private const CLIENT_ID_ARGUMENT = 'client-id';

    /**
     * Name of "client-secret" input option
     */
    private const CLIENT_SECRET_ARGUMENT = 'client-secret';

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
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $description = 'Enable or disable Adobe IMS Module.';

        $this->setName('admin:adobe-ims')
            ->setDescription($description)
            ->setDefinition([
                new InputArgument(
                    self::MODE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'The status of the module. Available options are "enable", "disable" or "status"'
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
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $mode = $input->getArgument(self::MODE_ARGUMENT);
            switch ($mode) {
                case self::MODE_DISABLE:
                    $this->disableAdobeImsModule($output);
                    $output->writeln(__('Admin Adobe IMS integration is disabled'));
                    break;
                case self::MODE_ENABLE:
                    $clientId = $input->getOption(self::CLIENT_ID_ARGUMENT);
                    $clientSecret = $input->getOption(self::CLIENT_SECRET_ARGUMENT);

                    if ($clientId === null) {
                        $helper = $this->getHelper('question');
                        $question = new Question('Please enter your Client ID:', '');
                        $question->setValidator(function ($value) {
                            if (trim($value) === '') {
                                throw new LocalizedException(__('The Client ID is required to enable the Admin Adobe IMS Module'));
                            }
                            return $value;
                        });

                        $clientId = $helper->ask($input, $output, $question);
                    }

                    if ($clientSecret === null) {
                        $helper = $this->getHelper('question');
                        $question = new Question('Please enter your Client Key:', '');
                        $question->setHidden(true);
                        $question->setHiddenFallback(false);
                        $question->setValidator(function ($value) {
                            if (trim($value) === '') {
                                throw new LocalizedException(__('The Client Secret is required to enable the Admin Adobe IMS Module'));
                            }
                            return $value;
                        });

                        $clientSecret = $helper->ask($input, $output, $question);
                    }

                    if ($clientId && $clientSecret) {
                        $enabled = $this->enableAdobeImsModule($output, $clientId, $clientSecret);
                        if ($enabled) {
                            $output->writeln(__('Admin Adobe IMS integration is enabled'));
                        }
                    } else {
                        throw new LocalizedException(__('The Client ID and Client Secret are required when enabling the Admin Adobe IMS Module'));
                    }
                    break;
                case self::MODE_STATUS:
                    $status = $this->getAdobeImsModuleStatus();
                    $output->writeln(__('Admin Adobe IMS integration is %1', $status));
                    break;
                default:
                    throw new LocalizedException(__('The mode can\'t be switched to "%1".', $mode));
            }

            return Cli::RETURN_SUCCESS;
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
     * @return string
     */
    private function getAdobeImsModuleStatus(): string
    {
        return $this->imsConfig->enabled() ? self::MODE_ENABLE .'d' : self::MODE_DISABLE.'d';
    }

    /**
     * Disable Admin Adobe IMS Module and unset Client ID and Client Secret from config
     *
     * @param OutputInterface $output
     * @return void
     */
    private function disableAdobeImsModule(OutputInterface $output): void
    {
        $this->imsConfig->updateConfig(
            ImsConfig::XML_PATH_ENABLED,
            '0'
        );

        $this->imsConfig->deleteConfig(ImsConfig::XML_PATH_API_KEY);
        $this->imsConfig->deleteConfig(ImsConfig::XML_PATH_PRIVATE_KEY);
    }

    /**
     * Enable Admin Adobe IMS Module and set Client ID and Client Secret when testConnection was successfully
     *
     * @param OutputInterface $output
     * @param string $clientId
     * @param string $clientSecret
     * @return bool
     * @throws InvalidArgumentException
     */
    private function enableAdobeImsModule(
        OutputInterface $output,
        string $clientId,
        string $clientSecret
    ): bool {
        $testAuth = $this->imsConnection->testAuth($clientId);

        if ($testAuth) {
            $this->imsConfig->updateConfig(
                ImsConfig::XML_PATH_ENABLED,
                '1'
            );

            $this->imsConfig->updateSecureConfig(
                ImsConfig::XML_PATH_API_KEY,
                $clientId
            );

            $this->imsConfig->updateSecureConfig(
                ImsConfig::XML_PATH_PRIVATE_KEY,
                $clientSecret
            );

            return true;
        }

        return false;
    }
}
