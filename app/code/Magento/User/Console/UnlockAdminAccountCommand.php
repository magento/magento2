<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\User\Model\ResourceModel\User as AdminUser;

/**
 * Command for unlocking an account.
 * @since 2.0.0
 */
class UnlockAdminAccountCommand extends Command
{
    const ARGUMENT_ADMIN_USERNAME = 'username';
    const ARGUMENT_ADMIN_USERNAME_DESCRIPTION = 'The admin username to unlock';
    const COMMAND_ADMIN_ACCOUNT_UNLOCK = 'admin:user:unlock';
    const COMMAND_DESCRIPTION = 'Unlock Admin Account';
    const USER_ID = 'user_id';

    /**
     * @var AdminUser
     * @since 2.0.0
     */
    private $adminUser;

    /**
     * {@inheritdoc}
     *
     * @param AdminUser $userResource
     * @since 2.0.0
     */
    public function __construct(
        AdminUser $adminUser,
        $name = null
    ) {
        $this->adminUser = $adminUser;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adminUserName = $input->getArgument(self::ARGUMENT_ADMIN_USERNAME);
        $userData = $this->adminUser->loadByUsername($adminUserName);
        $outputMessage = sprintf('Couldn\'t find the user account "%s"', $adminUserName);
        if ($userData) {
            if (isset($userData[self::USER_ID]) && $this->adminUser->unlock($userData[self::USER_ID])) {
                $outputMessage = sprintf('The user account "%s" has been unlocked', $adminUserName);
            } else {
                $outputMessage = sprintf(
                    'The user account "%s" was not locked or could not be unlocked',
                    $adminUserName
                );
            }
        }
        $output->writeln('<info>' . $outputMessage . '</info>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_ADMIN_ACCOUNT_UNLOCK);
        $this->setDescription(self::COMMAND_DESCRIPTION);
        $this->addArgument(
            self::ARGUMENT_ADMIN_USERNAME,
            InputArgument::REQUIRED,
            self::ARGUMENT_ADMIN_USERNAME_DESCRIPTION
        );
        $this->setHelp(
            <<<HELP
This command unlocks an admin account by its username.
To unlock:
      <comment>%command.full_name% username</comment>
HELP
        );
        parent::configure();
    }
}
