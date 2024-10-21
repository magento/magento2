<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\Setup\ConsoleLogger;
use Magento\Framework\Validation\ValidationException;
use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\InstallerFactory;
use Magento\User\Model\UserValidationRules;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command to create an admin user.
 */
class AdminUserCreateCommand extends AbstractSetupCommand
{
    public const NAME = 'admin:user:create';
    /**
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var UserValidationRules
     */
    private $validationRules;

    /**
     * @param InstallerFactory $installerFactory
     * @param UserValidationRules $validationRules
     */
    public function __construct(InstallerFactory $installerFactory, UserValidationRules $validationRules)
    {
        $this->installerFactory = $installerFactory;
        $this->validationRules = $validationRules;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Creates an administrator')
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * Creation admin user in interaction mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if (!$input->getOption(AdminAccount::KEY_USER)) {
            $question = new Question('<question>Admin user:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                AdminAccount::KEY_USER,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(AdminAccount::KEY_PASSWORD)) {
            $question = new Question('<question>Admin password:</question> ', '');
            $question->setHidden(true);

            $question->setValidator(function ($value) {
                $user = new \Magento\Framework\DataObject();
                $user->setPassword($value);

                $validator = new \Magento\Framework\Validator\DataObject();
                $this->validationRules->addPasswordRules($validator);

                $validator->isValid($user);
                foreach ($validator->getMessages() as $message) {
                    throw new ValidationException(__($message));
                }

                return $value;
            });

            $input->setOption(
                AdminAccount::KEY_PASSWORD,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(AdminAccount::KEY_EMAIL)) {
            $question = new Question('<question>Admin email:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                AdminAccount::KEY_EMAIL,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(AdminAccount::KEY_FIRST_NAME)) {
            $question = new Question('<question>Admin first name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                AdminAccount::KEY_FIRST_NAME,
                $questionHelper->ask($input, $output, $question)
            );
        }

        if (!$input->getOption(AdminAccount::KEY_LAST_NAME)) {
            $question = new Question('<question>Admin last name:</question> ', '');
            $this->addNotEmptyValidator($question);

            $input->setOption(
                AdminAccount::KEY_LAST_NAME,
                $questionHelper->ask($input, $output, $question)
            );
        }
    }

    /**
     * Add not empty validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addNotEmptyValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new ValidationException(__('The value cannot be empty'));
            }

            return $value;
        });
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $errors) . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installAdminUser($input->getOptions());
        $output->writeln(
            '<info>Created Magento administrator user named ' . $input->getOption(AdminAccount::KEY_USER) . '</info>'
        );
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Get list of arguments for the command
     *
     * @param int $mode The mode of options.
     * @return InputOption[]
     */
    public function getOptionsList($mode = InputOption::VALUE_REQUIRED)
    {
        $requiredStr = ($mode === InputOption::VALUE_REQUIRED ? '(Required) ' : '');

        return [
            new InputOption(
                AdminAccount::KEY_USER,
                null,
                $mode,
                $requiredStr . 'Admin user'
            ),
            new InputOption(
                AdminAccount::KEY_PASSWORD,
                null,
                $mode,
                $requiredStr . 'Admin password'
            ),
            new InputOption(
                AdminAccount::KEY_EMAIL,
                null,
                $mode,
                $requiredStr . 'Admin email'
            ),
            new InputOption(
                AdminAccount::KEY_FIRST_NAME,
                null,
                $mode,
                $requiredStr . 'Admin first name'
            ),
            new InputOption(
                AdminAccount::KEY_LAST_NAME,
                null,
                $mode,
                $requiredStr . 'Admin last name'
            ),
        ];
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $user = new \Magento\Framework\DataObject();
        $user->setFirstname($input->getOption(AdminAccount::KEY_FIRST_NAME))
            ->setLastname($input->getOption(AdminAccount::KEY_LAST_NAME))
            ->setUsername($input->getOption(AdminAccount::KEY_USER))
            ->setEmail($input->getOption(AdminAccount::KEY_EMAIL))
            ->setPassword(
                $input->getOption(AdminAccount::KEY_PASSWORD) === null
                ? '' : $input->getOption(AdminAccount::KEY_PASSWORD)
            );

        $validator = new \Magento\Framework\Validator\DataObject();
        $this->validationRules->addUserInfoRules($validator);
        $this->validationRules->addPasswordRules($validator);

        if (!$validator->isValid($user)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}
