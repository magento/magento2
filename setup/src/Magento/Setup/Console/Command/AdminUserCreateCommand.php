<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\AdminAccount;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Magento\User\Model\UserValidationRules;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class \Magento\Setup\Console\Command\AdminUserCreateCommand
 *
 * @since 2.0.0
 */
class AdminUserCreateCommand extends AbstractSetupCommand
{
    /**
     * @var InstallerFactory
     * @since 2.0.0
     */
    private $installerFactory;

    /**
     * @var UserValidationRules
     * @since 2.0.0
     */
    private $validationRules;

    /**
     * @param InstallerFactory $installerFactory
     * @param UserValidationRules $validationRules
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('admin:user:create')
            ->setDescription('Creates an administrator')
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL .  '<error>', $errors) . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installAdminUser($input->getOptions());
        $output->writeln(
            '<info>Created Magento administrator user named ' . $input->getOption(AdminAccount::KEY_USER) . '</info>'
        );
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     * @since 2.0.0
     */
    public function getOptionsList()
    {
        return [
            new InputOption(AdminAccount::KEY_USER, null, InputOption::VALUE_REQUIRED, '(Required) Admin user'),
            new InputOption(AdminAccount::KEY_PASSWORD, null, InputOption::VALUE_REQUIRED, '(Required) Admin password'),
            new InputOption(AdminAccount::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, '(Required) Admin email'),
            new InputOption(
                AdminAccount::KEY_FIRST_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                '(Required) Admin first name'
            ),
            new InputOption(
                AdminAccount::KEY_LAST_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                '(Required) Admin last name'
            ),
        ];
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     * @since 2.0.0
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

        $validator = new \Magento\Framework\Validator\DataObject;
        $this->validationRules->addUserInfoRules($validator);
        $this->validationRules->addPasswordRules($validator);

        if (!$validator->isValid($user)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}
