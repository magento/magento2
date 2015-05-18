<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Setup\Model\InstallerFactory;
use Magento\User\Model\UserValidationRules;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminUserCreateCommand extends AbstractSetupCommand
{
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
        $this->setName('admin:user:create')
            ->setDescription('Creates admin user')
            ->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL .  '<error>', $errors) . '</error>');
            return;
        }
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installAdminUser($input->getOptions());
        $output->writeln('<info>Created admin user ' . $input->getOption(AdminAccount::KEY_USER) . '</info>');
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList()
    {
        return [
            new InputOption(AdminAccount::KEY_USER, null, InputOption::VALUE_REQUIRED, 'Admin user'),
            new InputOption(AdminAccount::KEY_PASSWORD, null, InputOption::VALUE_REQUIRED, 'Admin password', ''),
            new InputOption(AdminAccount::KEY_EMAIL, null, InputOption::VALUE_REQUIRED, 'Admin email'),
            new InputOption(AdminAccount::KEY_FIRST_NAME, null, InputOption::VALUE_REQUIRED, 'Admin first name'),
            new InputOption(AdminAccount::KEY_LAST_NAME, null, InputOption::VALUE_REQUIRED, 'Admin last name'),
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
        $user = new \Magento\Framework\Object();
        $user->setFirstname($input->getOption(AdminAccount::KEY_FIRST_NAME))
            ->setLastname($input->getOption(AdminAccount::KEY_LAST_NAME))
            ->setUsername($input->getOption(AdminAccount::KEY_USER))
            ->setEmail($input->getOption(AdminAccount::KEY_EMAIL))
            ->setPassword($input->getOption(AdminAccount::KEY_PASSWORD));

        $validator = new \Magento\Framework\Validator\Object;
        $this->validationRules->addUserInfoRules($validator);
        $this->validationRules->addPasswordRules($validator);

        if (!$validator->isValid($user)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}
