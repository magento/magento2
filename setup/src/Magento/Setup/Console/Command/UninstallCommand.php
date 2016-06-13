<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;
use Magento\Framework\Code\GeneratedFiles;

class UninstallCommand extends AbstractSetupCommand
{
    /**
     * @var InstallerFactory
     */
    private $installerFactory;

    /**
     * @var GeneratedFiles
     */
    private $generatedFiles;

    /**
     * @param InstallerFactory $installerFactory
     */
    public function __construct(InstallerFactory $installerFactory, GeneratedFiles $generatedFiles)
    {
        $this->installerFactory = $installerFactory;
        $this->generatedFiles = $generatedFiles;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup:uninstall')
            ->setDescription('Uninstalls the Magento application');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to uninstall Magento?[y/N]', false);

        if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
            $installer = $this->installerFactory->create(new ConsoleLogger($output));
            $installer->uninstall();
            $this->generatedFiles->requestRegeneration();
        }
    }
}
