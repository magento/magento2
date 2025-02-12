<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\Framework\Setup\Declaration\Schema\Request;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\ConfigModel;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\SearchConfigOptionsList;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Command to install Magento application
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallCommand extends AbstractSetupCommand
{
    /**
     * Parameter indicating command whether to cleanup database in the install routine
     */
    public const INPUT_KEY_CLEANUP_DB = 'cleanup-database';

    /**
     * Parameter to specify an order_increment_prefix
     */
    public const INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX = 'sales-order-increment-prefix';

    /**
     * Parameter indicating command whether to install Sample Data
     */
    public const INPUT_KEY_USE_SAMPLE_DATA = 'use-sample-data';

    /**
     * List of comma-separated module names. That must be enabled during installation.
     * Available magic param all.
     */
    public const INPUT_KEY_ENABLE_MODULES = 'enable-modules';

    /**
     * List of comma-separated module names. That must be avoided during installation.
     * List of comma-separated module names. That must be avoided during installation.
     * Available magic param all.
     */
    public const INPUT_KEY_DISABLE_MODULES = 'disable-modules';

    /**
     * If this flag is enabled, than all your old scripts with format:
     * InstallSchema, UpgradeSchema will be converted to new db_schema.xml format.
     */
    public const CONVERT_OLD_SCRIPTS_KEY = 'convert-old-scripts';

    /**
     * Parameter indicating command for interactive setup
     */
    public const INPUT_KEY_INTERACTIVE_SETUP = 'interactive';

    /**
     * Parameter indicating command shortcut for interactive setup
     */
    public const INPUT_KEY_INTERACTIVE_SETUP_SHORTCUT = 'i';

    /**
     * Parameter says that in this mode all destructive operations, like column removal will be dumped
     */
    public const INPUT_KEY_SAFE_INSTALLER_MODE = 'safe-mode';

    /**
     * Parameter allows to restore data, that was dumped with safe mode before
     */
    public const INPUT_KEY_DATA_RESTORE = 'data-restore';

    /**
     * Regex for sales_order_increment_prefix validation.
     */
    public const SALES_ORDER_INCREMENT_PREFIX_RULE = '/^.{0,20}$/';

    public const NAME = 'setup:install';

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * @var InstallStoreConfigurationCommand
     */
    protected $userConfig;

    /**
     * @var AdminUserCreateCommand
     */
    protected $adminUser;

    /**
     * @var SearchConfigOptionsList
     */
    protected $searchConfigOptionsList;

    /**
     * @var array
     */
    private $interactiveSetupUserInput;

    /**
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ConfigModel $configModel
     * @param InstallStoreConfigurationCommand $userConfig
     * @param AdminUserCreateCommand $adminUser
     * @param SearchConfigOptionsList $searchConfigOptionsList
     */
    public function __construct(
        InstallerFactory $installerFactory,
        ConfigModel $configModel,
        InstallStoreConfigurationCommand $userConfig,
        AdminUserCreateCommand $adminUser,
        SearchConfigOptionsList $searchConfigOptionsList
    ) {
        $this->installerFactory = $installerFactory;
        $this->configModel = $configModel;
        $this->userConfig = $userConfig;
        $this->adminUser = $adminUser;
        $this->searchConfigOptionsList = $searchConfigOptionsList;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $inputOptions = $this->configModel->getAvailableOptions();
        $inputOptions = array_merge($inputOptions, $this->userConfig->getOptionsList());
        $inputOptions = array_merge($inputOptions, $this->adminUser->getOptionsList(InputOption::VALUE_OPTIONAL));
        $inputOptions = array_merge($inputOptions, $this->searchConfigOptionsList->getOptionsList());
        $inputOptions = array_merge($inputOptions, [
            new InputOption(
                self::INPUT_KEY_CLEANUP_DB,
                null,
                InputOption::VALUE_NONE,
                'Cleanup the database before installation'
            ),
            new InputOption(
                self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX,
                null,
                InputOption::VALUE_REQUIRED,
                'Sales order number prefix'
            ),
            new InputOption(
                self::INPUT_KEY_USE_SAMPLE_DATA,
                null,
                InputOption::VALUE_NONE,
                'Use sample data'
            ),
            new InputOption(
                self::INPUT_KEY_ENABLE_MODULES,
                null,
                InputOption::VALUE_OPTIONAL,
                'List of comma-separated module names. That must be included during installation. '
                . 'Available magic param "all".'
            ),
            new InputOption(
                self::INPUT_KEY_DISABLE_MODULES,
                null,
                InputOption::VALUE_OPTIONAL,
                'List of comma-separated module names. That must be avoided during installation. '
                . 'Available magic param "all".'
            ),
            new InputOption(
                self::CONVERT_OLD_SCRIPTS_KEY,
                null,
                InputOption::VALUE_OPTIONAL,
                'Allows to convert old scripts (InstallSchema, UpgradeSchema) to db_schema.xml format',
                false
            ),
            new InputOption(
                self::INPUT_KEY_INTERACTIVE_SETUP,
                self::INPUT_KEY_INTERACTIVE_SETUP_SHORTCUT,
                InputOption::VALUE_NONE,
                'Interactive Magento installation'
            ),
            new InputOption(
                OperationsExecutor::KEY_SAFE_MODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Safe installation of Magento with dumps on destructive operations, like column removal'
            ),
            new InputOption(
                OperationsExecutor::KEY_DATA_RESTORE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Restore removed data from dumps'
            ),
            new InputOption(
                DryRunLogger::INPUT_KEY_DRY_RUN_MODE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Magento Installation will be run in dry-run mode',
                false
            ),
        ]);
        $this->setName(self::NAME)
            ->setDescription('Installs the Magento application')
            ->setDefinition($inputOptions);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $installer = $this->installerFactory->create($consoleLogger);
        $isInteractiveSetup = $input->getOption(self::INPUT_KEY_INTERACTIVE_SETUP) ?? false;
        $installer->install($isInteractiveSetup ? array_merge($input->getOptions(), $this->interactiveSetupUserInput) :
            $input->getOptions());

        $importConfigCommand = $this->getApplication()->find(ConfigImportCommand::COMMAND_NAME);
        $arrayInput = new ArrayInput([]);
        $arrayInput->setInteractive($input->isInteractive());
        return $importConfigCommand->run($arrayInput, $output);
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();

        if ($inputOptions['interactive']) {
            $configOptionsToValidate = $this->interactiveQuestions($input, $output);
        } else {
            $configOptionsToValidate = [];
            foreach ($this->configModel->getAvailableOptions() as $option) {
                if (array_key_exists($option->getName(), $inputOptions)) {
                    $configOptionsToValidate[$option->getName()] = $inputOptions[$option->getName()];
                }
            }
        }

        if ($inputOptions['interactive']) {
            $command = '';
            foreach ($configOptionsToValidate as $key => $value) {
                $command .= " --{$key}={$value}";
            }
            $output->writeln("<comment>Try re-running command: php bin/magento setup:install{$command}</comment>");
            $this->interactiveSetupUserInput = $configOptionsToValidate;
        }

        $errors = $this->configModel->validate($configOptionsToValidate);
        $errors = array_merge($errors, $this->validateAdmin($input));
        $errors = array_merge($errors, $this->validate($input));
        $errors = array_merge($errors, $this->userConfig->validate($input));

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            throw new \InvalidArgumentException('Parameter validation failed');
        }
    }

    /**
     * Validate sales_order_increment_prefix value
     *
     * It will save the value which discarding characters after 20th to the database so it should be
     * validated in advance.
     *
     * @param InputInterface $input
     * @return string[] Array of error messages
     */
    public function validate(InputInterface $input) : array
    {
        $errors = [];
        $value = $input->getOption(self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX);
        if (preg_match(self::SALES_ORDER_INCREMENT_PREFIX_RULE, (string) $value) != 1) {
            $errors[] = 'Validation failed, ' . self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX
                . ' must be 20 characters or less';
        }
        return $errors;
    }

    /**
     * Runs interactive questions
     *
     * It will ask users for interactive questionst regarding setup configuration.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string[] Array of inputs
     */
    private function interactiveQuestions(InputInterface $input, OutputInterface $output) : array
    {
        $helper = $this->getHelper('question');
        $configOptionsToValidate = [];

        foreach ($this->configModel->getAvailableOptions() as $option) {
            $configOptionsToValidate[$option->getName()] = $this->askQuestion(
                $input,
                $output,
                $helper,
                $option,
                true
            );
        }

        $output->writeln("");

        foreach ($this->userConfig->getOptionsList() as $option) {
            $configOptionsToValidate[$option->getName()] = $this->askQuestion(
                $input,
                $output,
                $helper,
                $option
            );
        }

        $output->writeln("");

        foreach ($this->adminUser->getOptionsList(InputOption::VALUE_OPTIONAL) as $option) {
            $configOptionsToValidate[$option->getName()] = $this->askQuestion(
                $input,
                $output,
                $helper,
                $option
            );
        }

        $output->writeln("");

        $returnConfigOptionsToValidate = [];
        foreach ($configOptionsToValidate as $key => $value) {
            if ($value != '') {
                $returnConfigOptionsToValidate[$key] = $value;
            }
        }

        return $returnConfigOptionsToValidate;
    }

    /**
     * Runs interactive questions
     *
     * It will ask users for interactive questions regarding setup configuration.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     * @param TextConfigOption|FlagConfigOption\SelectConfigOption $option
     * @param Boolean $validateInline
     * @return string[] Array of inputs
     */
    private function askQuestion(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper,
        $option,
        $validateInline = false
    ) {
        if ($option instanceof \Magento\Framework\Setup\Option\SelectConfigOption) {
            if ($option->isValueRequired()) {
                $question = new ChoiceQuestion(
                    $option->getDescription() . '? ',
                    $option->getSelectOptions(),
                    $option->getDefault()
                );
            } else {
                $question = new ChoiceQuestion(
                    $option->getDescription() . ' [optional]? ',
                    $option->getSelectOptions(),
                    $option->getDefault()
                );
            }
        } else {
            if ($option->isValueRequired()) {
                $question = new Question(
                    $option->getDescription() . '? ',
                    $option->getDefault()
                );
            } else {
                $question = new Question(
                    $option->getDescription() . ' [optional]? ',
                    $option->getDefault()
                );
            }
        }

        $question->setValidator(function ($answer) use ($option, $validateInline) {

            if ($option instanceof \Magento\Framework\Setup\Option\SelectConfigOption) {
                //If user doesn't provide an input & default value for question is not set, take first option as input.
                $answer = $option->getSelectOptions()[$answer] ?? current($option->getSelectOptions());
            }

            $answer = $answer === null ? '' : (is_string($answer) ? trim($answer) : $answer);

            if ($validateInline) {
                $option->validate($answer);
            }

            return $answer;
        });

        $value = $helper->ask($input, $output, $question);

        return $value;
    }

    /**
     * Performs validation of admin options if at least one of them was set.
     *
     * @param InputInterface $input
     * @return array
     */
    private function validateAdmin(InputInterface $input): array
    {
        if ($input->getOption(AdminAccount::KEY_FIRST_NAME)
            || $input->getOption(AdminAccount::KEY_LAST_NAME)
            || $input->getOption(AdminAccount::KEY_EMAIL)
            || $input->getOption(AdminAccount::KEY_USER)
            || $input->getOption(AdminAccount::KEY_PASSWORD)
        ) {
            return $this->adminUser->validate($input);
        }

        return [];
    }
}
