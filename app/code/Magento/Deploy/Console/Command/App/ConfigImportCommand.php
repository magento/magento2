<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Deploy\Console\Command\App\ConfigImport\Processor;

/**
 * Runs the process of importing configuration data from shared source to appropriate application sources
 *
 * We have configuration files that are shared between environments, but some of the configurations are read only
 * from DB (e.g., themes, scopes and etc). This command is used to import such configurations from the file to
 * appropriate application sources
 * @since 2.2.0
 */
class ConfigImportCommand extends Command
{
    /**
     * Command name.
     */
    const COMMAND_NAME = 'app:config:import';

    /**
     * Configuration importer.
     *
     * @var Processor
     * @since 2.2.0
     */
    private $processor;

    /**
     * @param Processor $processor the configuration importer
     * @since 2.2.0
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;

        parent::__construct();
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Import data from shared configuration files to appropriate data storage');

        parent::configure();
    }

    /**
     * Imports data from deployment configuration files to the DB. {@inheritdoc}
     * @since 2.2.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->processor->execute($input, $output);
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
