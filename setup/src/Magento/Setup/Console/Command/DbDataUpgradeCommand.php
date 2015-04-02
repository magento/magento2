<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Setup\Model\InstallerFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for install and update of data in DB
 */
class DbDataUpgradeCommand extends Command
{
    /**
     * Factory to create logger
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Factory to create installer
     *
     * @var InstallerFactory
     */
    private $installFactory;

    /**
     * Inject dependencies
     *
     * @param InstallerFactory $installFactory
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(InstallerFactory $installFactory, ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->installFactory = $installFactory;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('setup:db-data:upgrade')->setDescription('Install and upgrade data in DB');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = $this->objectManagerProvider->get()->create('Magento\Setup\Model\ConsoleLogger', ['output' => $output]);
        $installer = $this->installFactory->create($log);
        $installer->installDataFixtures();
    }
}
