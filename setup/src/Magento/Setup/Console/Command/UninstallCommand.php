<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Setup\Model\InstallerFactory;
use Magento\Framework\Setup\ConsoleLogger;

/**
 * Class \Magento\Setup\Console\Command\UninstallCommand
 *
 * @since 2.0.0
 */
class UninstallCommand extends AbstractSetupCommand
{
    /**
     * @var InstallerFactory
     * @since 2.0.0
     */
    private $installerFactory;

    /**
     * @param InstallerFactory $installerFactory
     * @since 2.0.0
     */
    public function __construct(InstallerFactory $installerFactory)
    {
        $this->installerFactory = $installerFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('setup:uninstall')
            ->setDescription('Uninstalls the Magento application');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to uninstall Magento?[y/N]', false);

        if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
            $installer = $this->installerFactory->create(new ConsoleLogger($output));
            $installer->uninstall();
        }
    }
}
