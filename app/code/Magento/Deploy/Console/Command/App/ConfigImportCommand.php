<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command\App;

use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Deploy\Console\Command\App\ConfigImport\Processor;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the process of importing configuration data from shared source to appropriate application sources
 *
 * We have configuration files that are shared between environments, but some of the configurations are read only
 * from DB (e.g., themes, scopes and etc). This command is used to import such configurations from the file to
 * appropriate application sources
 */
class ConfigImportCommand extends Command
{
    const COMMAND_NAME = 'app:config:import';

    /**
     * Configuration importer.
     *
     * @var Processor
     */
    private $processor;

    /**
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $adminhtmlAreaProcessor;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @param Processor $processor the configuration importer
     * @param DeploymentConfig|null $deploymentConfig
     * @param EmulatedAdminhtmlAreaProcessor|null $adminhtmlAreaProcessor
     * @param AreaList|null $areaList
     */
    public function __construct(
        Processor $processor,
        ?DeploymentConfig $deploymentConfig = null,
        ?EmulatedAdminhtmlAreaProcessor $adminhtmlAreaProcessor = null,
        ?AreaList $areaList = null
    ) {
        $this->processor = $processor;
        $this->deploymentConfig = $deploymentConfig
            ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->adminhtmlAreaProcessor = $adminhtmlAreaProcessor
            ?? ObjectManager::getInstance()->get(EmulatedAdminhtmlAreaProcessor::class);
        $this->areaList = $areaList
            ?? ObjectManager::getInstance()->get(AreaList::class);

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
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($this->canEmulateAdminhtmlArea()) {
                // Emulate adminhtml area in order to execute all needed plugins declared only for this area
                // For instance URL rewrite generation during creating store view
                $this->adminhtmlAreaProcessor->process(function () use ($input, $output) {
                    $this->processor->execute($input, $output);
                });
            } else {
                $this->processor->execute($input, $output);
            }
        } catch (RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Detects if we can emulate adminhtml area
     *
     * This area could be not available for instance during setup:install
     *
     * @return bool
     * @throws RuntimeException
     * @throws FileSystemException
     */
    private function canEmulateAdminhtmlArea(): bool
    {
        return $this->deploymentConfig->isAvailable()
            && in_array(Area::AREA_ADMINHTML, $this->areaList->getCodes());
    }
}
