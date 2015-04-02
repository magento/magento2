<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\InstallerFactory;

class UpdateCommand extends Command
{
    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Installer service factory
     *
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param InstallerFactory $installerFactory
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        InstallerFactory $installerFactory
    )
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->installerFactory = $installerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:update')
            ->setDescription('Updates installed application after the code base has changed, '
                . 'including DB schema and data install/upgrade');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = $this->objectManagerProvider->get()->create(
            'Magento\Setup\Model\ConsoleLogger',
            ['output' => $output]
        );
        $installer = $this->installerFactory->create($consoleLogger);
        $installer->updateModulesSequence();
        $installer->installSchema();
        $installer->installDataFixtures();
    }
}
