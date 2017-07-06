<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Deploy\Console\Command\App\SensitiveConfigSet\SensitiveConfigSetFacade;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for set sensitive variable through deploy process
 */
class SensitiveConfigSetCommand extends Command
{
    /**
     * Name of "interactive" input option
     */
    const INPUT_OPTION_INTERACTIVE = 'interactive';

    /**
     * Name of "configuration scope" input option
     */
    const INPUT_OPTION_SCOPE = 'scope';

    /**
     * Name of "configuration scope code" input option
     */
    const INPUT_OPTION_SCOPE_CODE = 'scope-code';

    /**
     * Name of "configuration path" input argument
     */
    const INPUT_ARGUMENT_PATH = 'path';

    /**
     * Name of "configuration value" input argument
     */
    const INPUT_ARGUMENT_VALUE = 'value';

    /**
     * The config change detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * The hash manager.
     *
     * @var Hash
     */
    private $hash;

    /**
     * The facade for command.
     *
     * @var SensitiveConfigSetFacade
     */
    private $facade;

    /**
     * Emulator adminhtml area for CLI command.
     *
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $emulatedAreaProcessor;

    /**
     * @param SensitiveConfigSetFacade $facade The processor facade
     * @param ChangeDetector $changeDetector The config change detector
     * @param Hash $hash The hash manager
     * @param EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor Emulator adminhtml area for CLI command
     */
    public function __construct(
        SensitiveConfigSetFacade $facade,
        ChangeDetector $changeDetector,
        Hash $hash,
        EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor
    ) {
        $this->facade = $facade;
        $this->changeDetector = $changeDetector;
        $this->hash = $hash;
        $this->emulatedAreaProcessor = $emulatedAreaProcessor;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_ARGUMENT_PATH,
            InputArgument::OPTIONAL,
            'Configuration path for example section/group/field_name'
        );
        $this->addArgument(
            self::INPUT_ARGUMENT_VALUE,
            InputArgument::OPTIONAL,
            'Configuration value'
        );
        $this->addOption(
            self::INPUT_OPTION_INTERACTIVE,
            'i',
            InputOption::VALUE_NONE,
            'Enable interactive mode to set all sensitive variables'
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope for configuration, if not set use \'default\'',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE_CODE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope code for configuration, empty string by default',
            ''
        );
        $this->setName('config:sensitive:set')
            ->setDescription('Set sensitive configuration values');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            $this->emulatedAreaProcessor->process(function () use ($input, $output) {
                $this->facade->process($input, $output);
                $this->hash->regenerate(System::CONFIG_TYPE);
            });

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s</error>', $e->getMessage())
            );

            return Cli::RETURN_FAILURE;
        }
    }
}
