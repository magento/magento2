<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\State;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;

/**
 * A class to manage Magento modes
 *
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 * @SuppressWarnings("PMD.ExcessiveParameterList")
 */
class Mode
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Writer $writer
     * @param Reader $reader
     * @param MaintenanceMode $maintenanceMode
     * @param Filesystem $filesystem
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Writer $writer,
        Reader $reader,
        MaintenanceMode $maintenanceMode,
        Filesystem $filesystem
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->writer = $writer;
        $this->reader = $reader;
        $this->maintenanceMode = $maintenanceMode;
        $this->filesystem = $filesystem;
    }

    /**
     * Enable production mode
     *
     * @return void
     */
    public function enableProductionMode()
    {
        $this->enableMaintenanceMode($this->output);
        $this->filesystem->regenerateStatic($this->output);
        $this->setStoreMode(State::MODE_PRODUCTION);
        $this->disableMaintenanceMode($this->output);
    }

    /**
     * Only lock static resource locations and set store mode, without handling static content
     *
     * @return void
     */
    public function enableProductionModeMinimal()
    {
        $this->setStoreMode(State::MODE_PRODUCTION);
    }

    /**
     * Enable Developer mode
     *
     * @return void
     */
    public function enableDeveloperMode()
    {
        $this->filesystem->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATION,
                DirectoryList::DI,
                DirectoryList::TMP_MATERIALIZATION_DIR,
                DirectoryList::STATIC_VIEW,
            ]
        );
        $this->setStoreMode(State::MODE_DEVELOPER);
    }

    /**
     * Get current mode information
     *
     * @return string
     * @throws \Exception
     */
    public function getMode()
    {
        $env = $this->reader->load(ConfigFilePool::APP_ENV);
        return isset($env[State::PARAM_MODE]) ? $env[State::PARAM_MODE] : null;
    }

    /**
     * Store mode in env.php
     *
     * @param string $mode
     * @return void
     */
    protected function setStoreMode($mode)
    {
        $data = [
            ConfigFilePool::APP_ENV => [
                State::PARAM_MODE => $mode
            ]
        ];
        $this->writer->saveConfig($data);
    }

    /**
     * Enable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function enableMaintenanceMode(OutputInterface $output)
    {
        $this->maintenanceMode->set(true);
        $output->writeln('Enabled maintenance mode');
    }

    /**
     * Disable maintenance mode
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function disableMaintenanceMode(OutputInterface $output)
    {
        $this->maintenanceMode->set(false);
        $output->writeln('Disabled maintenance mode');
    }
}
