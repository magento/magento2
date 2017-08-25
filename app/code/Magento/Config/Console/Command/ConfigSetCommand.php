<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
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
 * @since 100.2.0
 */
class ConfigSetCommand extends Command
{
    /**#@+
     * Constants for arguments and options.
     */
    const ARG_PATH = 'path';
    const ARG_VALUE = 'value';
    const OPTION_SCOPE = 'scope';
    const OPTION_SCOPE_CODE = 'scope-code';
    const OPTION_LOCK = 'lock';
    /**#@-*/

    /**#@-*/
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
     * @param EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor Emulator adminhtml area for CLI command
     * @param ChangeDetector $changeDetector The config change detector
     * @param ProcessorFacadeFactory $processorFacadeFactory The factory for processor facade
     * @param DeploymentConfig $deploymentConfig Application deployment configuration
     * @since 100.2.0
     */
    public function __construct(
        EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor,
        ChangeDetector $changeDetector,
        ProcessorFacadeFactory $processorFacadeFactory,
        DeploymentConfig $deploymentConfig
    ) {
        $this->emulatedAreaProcessor = $emulatedAreaProcessor;
        $this->changeDetector = $changeDetector;
        $this->processorFacadeFactory = $processorFacadeFactory;
        $this->deploymentConfig = $deploymentConfig;

        parent::__construct();
    }

    /**
     * @inheritdoc
     * @since 100.2.0
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
                    static::OPTION_LOCK,
                    'l',
                    InputOption::VALUE_NONE,
                    'Lock value which prevents modification in the Admin'
                ),
            ]);

        parent::configure();
    }

    /**
     * Creates and run appropriate processor, depending on input options.
     *
     * {@inheritdoc}
     * @since 100.2.0
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
                return $this->processorFacadeFactory->create()->process(
                    $input->getArgument(static::ARG_PATH),
                    $input->getArgument(static::ARG_VALUE),
                    $input->getOption(static::OPTION_SCOPE),
                    $input->getOption(static::OPTION_SCOPE_CODE),
                    $input->getOption(static::OPTION_LOCK)
                );
            });

            $output->writeln('<info>' . $message . '</info>');

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }
    }
}
