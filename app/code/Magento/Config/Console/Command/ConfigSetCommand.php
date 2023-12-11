<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Console\Command;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command provides possibility to change system configuration.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class ConfigSetCommand extends Command
{
    /**#@+
     * Constants for arguments and options.
     */
    public const ARG_PATH = 'path';
    public const ARG_VALUE = 'value';
    public const OPTION_SCOPE = 'scope';
    public const OPTION_SCOPE_CODE = 'scope-code';
    public const OPTION_LOCK = 'lock';
    public const OPTION_LOCK_ENV = 'lock-env';
    public const OPTION_LOCK_CONFIG = 'lock-config';
    /**#@-*/

    /**#@-
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $emulatedAreaProcessor;

    /**
     * The config change detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * The factory for processor facade.
     *
     * @var ProcessorFacadeFactory
     */
    private $processorFacadeFactory;

    /**
     * Application deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var LocaleEmulatorInterface
     */
    private $localeEmulator;

    /**
     * @param EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor Emulator adminhtml area for CLI command
     * @param ChangeDetector $changeDetector The config change detector
     * @param ProcessorFacadeFactory $processorFacadeFactory The factory for processor facade
     * @param DeploymentConfig $deploymentConfig Application deployment configuration
     * @param LocaleEmulatorInterface|null $localeEmulator
     */
    public function __construct(
        EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor,
        ChangeDetector $changeDetector,
        ProcessorFacadeFactory $processorFacadeFactory,
        DeploymentConfig $deploymentConfig,
        LocaleEmulatorInterface $localeEmulator = null
    ) {
        $this->emulatedAreaProcessor = $emulatedAreaProcessor;
        $this->changeDetector = $changeDetector;
        $this->processorFacadeFactory = $processorFacadeFactory;
        $this->deploymentConfig = $deploymentConfig;
        $this->localeEmulator = $localeEmulator;

        parent::__construct();
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    protected function configure()
    {
        $this->setName('config:set')
            ->setDescription('Change system configuration')
            ->setDefinition([
                new InputArgument(
                    static::ARG_PATH,
                    InputArgument::REQUIRED,
                    'Configuration path in format section/group/field_name'
                ),
                new InputArgument(static::ARG_VALUE, InputArgument::REQUIRED, 'Configuration value'),
                new InputOption(
                    static::OPTION_SCOPE,
                    null,
                    InputArgument::OPTIONAL,
                    'Configuration scope (default, website, or store)',
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                ),
                new InputOption(
                    static::OPTION_SCOPE_CODE,
                    null,
                    InputArgument::OPTIONAL,
                    'Scope code (required only if scope is not \'default\')'
                ),
                new InputOption(
                    static::OPTION_LOCK_ENV,
                    'e',
                    InputOption::VALUE_NONE,
                    'Lock value which prevents modification in the Admin (will be saved in app/etc/env.php)'
                ),
                new InputOption(
                    static::OPTION_LOCK_CONFIG,
                    'c',
                    InputOption::VALUE_NONE,
                    'Lock and share value with other installations, prevents modification in the Admin '
                    . '(will be saved in app/etc/config.php)'
                ),
                new InputOption(
                    static::OPTION_LOCK,
                    'l',
                    InputOption::VALUE_NONE,
                    'Deprecated, use the --' . static::OPTION_LOCK_ENV . ' option instead.'
                ),
            ]);

        parent::configure();
    }

    /**
     * Creates and run appropriate processor, depending on input options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws FileSystemException
     * @throws RuntimeException
     * @since 101.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln(
                '<error>You cannot run this command because the Magento application is not installed.</error>'
            );
            return Cli::RETURN_FAILURE;
        }
        if ($this->changeDetector->hasChanges(System::CONFIG_TYPE)) {
            $output->writeln(
                '<error>'
                . 'This command is unavailable right now. '
                . 'To continue working with it please run app:config:import or setup:upgrade command before.'
                . '</error>'
            );

            return Cli::RETURN_FAILURE;
        }

        try {
            $message = $this->emulatedAreaProcessor->process(function () use ($input) {
                return $this->localeEmulator->emulate(function () use ($input) {
                    $lock = $input->getOption(static::OPTION_LOCK_ENV)
                        || $input->getOption(static::OPTION_LOCK_CONFIG)
                        || $input->getOption(static::OPTION_LOCK);

                    $lockTargetPath = ConfigFilePool::APP_ENV;
                    if ($input->getOption(static::OPTION_LOCK_CONFIG)) {
                        $lockTargetPath = ConfigFilePool::APP_CONFIG;
                    }

                    return $this->processorFacadeFactory->create()->processWithLockTarget(
                        $input->getArgument(static::ARG_PATH),
                        $input->getArgument(static::ARG_VALUE),
                        $input->getOption(static::OPTION_SCOPE),
                        $input->getOption(static::OPTION_SCOPE_CODE),
                        $lock,
                        $lockTargetPath
                    );
                });
            });

            $output->writeln('<info>' . $message . '</info>');

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return Cli::RETURN_FAILURE;
        }
    }
}
