<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Model\Config\PathValidatorFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
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
     * The factory for config:set processors.
     *
     * @var ConfigSetProcessorFactory
     */
    private $configSetProcessorFactory;

    /**
     * Scope validator.
     *
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Scope manager.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * The factory for path validator.
     *
     * @var PathValidatorFactory
     */
    private $pathValidatorFactory;

    /**
     * @param ConfigSetProcessorFactory $configSetProcessorFactory The factory for config:set processors
     * @param ValidatorInterface $validator Scope validator
     * @param ScopeInterface $scope Scope manager
     * @param PathValidatorFactory $pathValidatorFactory The factory for path validator
     */
    public function __construct(
        ConfigSetProcessorFactory $configSetProcessorFactory,
        ValidatorInterface $validator,
        ScopeInterface $scope,
        PathValidatorFactory $pathValidatorFactory
    ) {
        $this->configSetProcessorFactory = $configSetProcessorFactory;
        $this->validator = $validator;
        $this->scope = $scope;
        $this->pathValidatorFactory = $pathValidatorFactory;

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
            $this->validator->isValid(
                $input->getOption(static::OPTION_SCOPE),
                $input->getOption(static::OPTION_SCOPE_CODE)
            );

            // Emulating adminhtml scope to be able to read configs.
            $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

            /**
             * Validates the entered config path.
             * Requires emulated area.
             */
            $this->pathValidatorFactory->create()->validate(
                $input->getArgument(ConfigSetCommand::ARG_PATH)
            );

            $processor = $input->getOption(static::OPTION_LOCK)
                ? $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_LOCK)
                : $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_DEFAULT);
            $message = $input->getOption(static::OPTION_LOCK)
                ? 'Value was saved and locked.'
                : 'Value was saved.';

            // The processing flow depends on --lock option.
            $processor->process(
                $input->getArgument(ConfigSetCommand::ARG_PATH),
                $input->getArgument(ConfigSetCommand::ARG_VALUE),
                $input->getOption(ConfigSetCommand::OPTION_SCOPE),
                $input->getOption(ConfigSetCommand::OPTION_SCOPE_CODE)
            );

            $output->writeln('<info>' . $message . '</info>');

            return Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }
    }
}
