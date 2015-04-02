<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\AdminAccount;
use Magento\Setup\Model\ConsoleLogger;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdminUserCreateCommand extends Command
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @param InstallerFactory $installerFactory
     */
    public function __construct(InstallerFactory $installerFactory)
    {
        $this->installerFactory = $installerFactory;
        parent::__construct();
    }

    /**
     * Gets input arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            new InputArgument(AdminAccount::KEY_USER, InputArgument::REQUIRED, 'Admin user'),
            new InputArgument(AdminAccount::KEY_PASSWORD, InputArgument::REQUIRED, 'Admin password'),
            new InputArgument(AdminAccount::KEY_EMAIL, InputArgument::REQUIRED, 'Admin email'),
            new InputArgument(AdminAccount::KEY_FIRST_NAME, InputArgument::REQUIRED, 'Admin firstname'),
            new InputArgument(AdminAccount::KEY_LAST_NAME, InputArgument::REQUIRED, 'Admin lastname'),
        ];
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = $this->getArguments();

        $this->setName('admin:user:create')
            ->setDescription('Creates admin user')
            ->setDefinition($options);

        $this->ignoreValidationErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installer = $this->installerFactory->create(new ConsoleLogger($output));
        $installer->installAdminUser($input->getArguments());
    }
}
