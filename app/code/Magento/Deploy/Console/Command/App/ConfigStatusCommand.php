<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for checking if Config propagation is up to date
 */
class ConfigStatusCommand extends Command
{
    /**
     * Code for error when config import is required.
     */
    const EXIT_CODE_CONFIG_IMPORT_REQUIRED = 2;

    /**
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * ConfigStatusCommand constructor.
     * @param ChangeDetector $changeDetector
     */
    public function __construct(ChangeDetector $changeDetector)
    {
        $this->changeDetector = $changeDetector;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('app:config:status')
            ->setDescription('Checks if config propagation requires update');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->changeDetector->hasChanges()) {
            $output->writeln(
                '<info>Config files have changed. ' .
                'Run app:config:import or setup:upgrade command to synchronize configuration.</info>'
            );
            return self::EXIT_CODE_CONFIG_IMPORT_REQUIRED;
        }
        $output->writeln('<info>Config files are up to date.</info>');
        return Cli::RETURN_SUCCESS;
    }
}
