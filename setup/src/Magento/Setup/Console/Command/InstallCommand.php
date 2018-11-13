<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\AdminAccount;
use Symfony\Component\Console\Input\InputOption;
use Magento\Setup\Model\ConfigModel;
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
    const INPUT_KEY_CLEANUP_DB = 'cleanup-database';

    /**
     * Parameter to specify an order_increment_prefix
     */
    const INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX = 'sales-order-increment-prefix';

    /**
     * Parameter indicating command whether to install Sample Data
     */
    const INPUT_KEY_USE_SAMPLE_DATA = 'use-sample-data';

    /**
     * Parameter indicating command for interactive setup
     */
    const INPUT_KEY_INTERACTIVE_SETUP = 'interactive';

    /**
     * Parameter indicating command shortcut for interactive setup
     */
    const INPUT_KEY_INTERACTIVE_SETUP_SHORTCUT = 'i';

    /**
     * Regex for sales_order_increment_prefix validation.
     */
    const SALES_ORDER_INCREMENT_PREFIX_RULE = '/^.{0,20}$/';

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
     * Constructor
     *
     * @param InstallerFactory $installerFactory
     * @param ConfigModel $configModel
     * @param InstallStoreConfigurationCommand $userConfig
     * @param AdminUserCreateCommand $adminUser
     */
    public function __construct(
        InstallerFactory $installerFactory,
        ConfigModel $configModel,
        InstallStoreConfigurationCommand $userConfig,
        AdminUserCreateCommand $adminUser
    ) {
        $this->installerFactory = $installerFactory;
        $this->configModel = $configModel;
        $this->userConfig = $userConfig;
        $this->adminUser = $adminUser;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $inputOptions = $this->configModel->getAvailableOptions();
        $inputOptions = array_merge($inputOptions, $this->userConfig->getOptionsList());
        $inputOptions = array_merge($inputOptions, $this->adminUser->getOptionsList(InputOption::VALUE_OPTIONAL));
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
                self::INPUT_KEY_INTERACTIVE_SETUP,
                self::INPUT_KEY_INTERACTIVE_SETUP_SHORTCUT,
                InputOption::VALUE_NONE,
                'Interactive Magento instalation'
            ),
        ]);
        $this->setName('setup:install')
            ->setDescription('Installs the Magento application')
            ->setDefinition($inputOptions);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $installer = $this->installerFactory->create($consoleLogger);
        $installer->install($input->getOptions());

        $importConfigCommand = $this->getApplication()->find(ConfigImportCommand::COMMAND_NAME);
        $arrayInput = new ArrayInput([]);
        $arrayInput->setInteractive($input->isInteractive());
        $importConfigCommand->run($arrayInput, $output);
    }

    /**
     * {@inheritdoc}
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
    public function validate(InputInterface $input)
    {
        $errors = [];
        $value = $input->getOption(self::INPUT_KEY_SALES_ORDER_INCREMENT_PREFIX);
        if (preg_match(self::SALES_ORDER_INCREMENT_PREFIX_RULE, $value) != 1) {
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
    private function interactiveQuestions(InputInterface $input, OutputInterface $output)
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
     * It will ask users for interactive questionst regarding setup configuration.
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
                $answer = $option->getSelectOptions()[$answer];
            }

            if ($answer == null) {
                $answer = '';
            } else {
                $answer = trim($answer);
            }

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
