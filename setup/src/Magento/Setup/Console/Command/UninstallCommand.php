<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;

class UninstallCommand extends AbstractSetupCommand
{
    public const NAME = 'setup:uninstall';
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
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Uninstalls the Magento application');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to uninstall Magento?[y/N]', false);

        if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
            $installer = $this->installerFactory->create(new ConsoleLogger($output));
            $installer->uninstall();
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
