<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Model\DeploymentConfig\ImportFailedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Deploy\Console\Command\App\ConfigImport\Importer;
use Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Imports data from deployment configuration files to the DB.
 *
 * We have configuration files that are shared between environments, but some of the configurations are read only
 * from DB (e.g., themes, scopes and etc). This command is used to import such configurations from the file to DB.
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
     * @var Importer
     */
    private $importer;

    /**
     * Configuration file writer.
     *
     * @var Writer
     */
    private $writer;

    /**
     * @param Importer $importer the configuration importer
     * @param Writer $writer the configuration file writer
     */
    public function __construct(Importer $importer, Writer $writer)
    {
        $this->importer = $importer;
        $this->writer = $writer;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Import data from shared configuration files to appropriate data storage');

        parent::configure();
    }

    /**
     * Imports data from deployment configuration files to the DB.
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->writer->checkIfWritable()) {
            $output->writeln('<error>Deployment configuration file is not writable.</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            $this->importer->import($output);
        } catch (ImportFailedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
