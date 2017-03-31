<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command provides possibility to change system configuration.
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

    /**
     * Scope manager.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * Application state.
     *
     * @var State
     */
    private $state;

    /**
     * The processor facade factory
     *
     * @var ProcessorFacadeFactory
     */
    private $processorFacadeFactory;

    /**
     * @param ScopeInterface $scope Scope manager
     * @param State $state Application state
     * @param ProcessorFacadeFactory $processorFacadeFactory The processor facade factory
     */
    public function __construct(
        ScopeInterface $scope,
        State $state,
        ProcessorFacadeFactory $processorFacadeFactory
    ) {
        $this->scope = $scope;
        $this->state = $state;
        $this->processorFacadeFactory = $processorFacadeFactory;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('config:set')
            ->setDescription('Change system configuration')
            ->setDefinition([
                new InputArgument(
                    static::ARG_PATH,
                    InputArgument::REQUIRED,
                    'Configuration path in format group/section/field_name'
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $areaScope = $this->scope->getCurrentScope();
            // Emulating adminhtml scope to be able to read configs.
            $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($input, $output) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

                $message = $this->processorFacadeFactory->create()->process(
                    $input->getArgument(static::ARG_PATH),
                    $input->getArgument(static::ARG_VALUE),
                    $input->getOption(static::OPTION_SCOPE),
                    $input->getOption(static::OPTION_SCOPE_CODE),
                    $input->getOption(static::OPTION_LOCK)
                );

                $output->writeln('<info>' . $message . '</info>');
            });

            $this->scope->setCurrentScope($areaScope);

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }
    }
}
