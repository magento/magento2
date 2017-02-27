<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Magento\Deploy\Console\Command\App\ConfigImport\Importer;

/**
 * Imports data from deployment configuration files to the DB.
 *
 * We have configuration files that are shared between environments, but sometime we need read
 * some configurations only from the DB (e.g., themes, scopes and etc). This command check changes of specific sections
 * (which are defined in di.xml) in configuration files, and import them if are needed.
 */
class ConfigImportCommand extends Command
{
    /**
     * Command name.
     */
    const COMMAND_NAME = 'app:config:import';

    /**
     * @var Importer
     */
    private $importer;

    /**
     * @param Importer $importer
     */
    public function __construct(Importer $importer)
    {
        $this->importer = $importer;

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
        try {
            $this->importer->import($output);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
