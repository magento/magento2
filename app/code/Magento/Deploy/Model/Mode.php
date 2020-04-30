<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Model;

use Magento\Deploy\App\Mode\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A class to manage Magento modes
 *
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 * @SuppressWarnings("PMD.ExcessiveParameterList")
 */
class Mode
{
    /**
     * @var OutputInterface
     */
    private $output;

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
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * The factory for processor facade.
     *
     * @var ProcessorFacadeFactory
     */
    private $processorFacadeFactory;

    /**
     * Emulator adminhtml area for CLI command.
     *
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $emulatedAreaProcessor;

    /**
     * @var MaintenanceModeEnabler
     */
    private $maintenanceModeEnabler;

    /**
     * @param OutputInterface $output
     * @param Writer $writer
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ConfigProvider $configProvider
     * @param ProcessorFacadeFactory $processorFacadeFactory
     * @param EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor
     * @param MaintenanceModeEnabler $maintenanceModeEnabler
     */
    public function __construct(
        OutputInterface $output,
        Writer $writer,
        Reader $reader,
        Filesystem $filesystem,
        ConfigProvider $configProvider,
        ProcessorFacadeFactory $processorFacadeFactory,
        EmulatedAdminhtmlAreaProcessor $emulatedAreaProcessor,
        MaintenanceModeEnabler $maintenanceModeEnabler
    ) {
        $this->output = $output;
        $this->writer = $writer;
        $this->reader = $reader;
        $this->filesystem = $filesystem;

        $this->configProvider = $configProvider;
        $this->processorFacadeFactory = $processorFacadeFactory;
        $this->emulatedAreaProcessor = $emulatedAreaProcessor;
        $this->maintenanceModeEnabler = $maintenanceModeEnabler;
    }

    /**
     * Enable production mode
     *
     * @throws LocalizedException
     * @return void
     */
    public function enableProductionMode()
    {
        $this->maintenanceModeEnabler->executeInMaintenanceMode(
            function () {
                $previousMode = $this->getMode();
                try {
                    // We have to turn on production mode before generation.
                    // We need this to enable generation of the "min" files.
                    $this->setStoreMode(State::MODE_PRODUCTION);
                    $this->filesystem->regenerateStatic($this->output);
                } catch (LocalizedException $e) {
                    // We have to return store mode to previous state in case of error.
                    $this->setStoreMode($previousMode);
                    throw $e;
                }
            },
            $this->output,
            false
        );
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
                DirectoryList::GENERATED_CODE,
                DirectoryList::GENERATED_METADATA,
                DirectoryList::TMP_MATERIALIZATION_DIR,
                DirectoryList::STATIC_VIEW,
            ]
        );
        $this->setStoreMode(State::MODE_DEVELOPER);
    }

    /**
     * Enable Default mode.
     *
     * @return void
     */
    public function enableDefaultMode()
    {
        $this->filesystem->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATED_CODE,
                DirectoryList::GENERATED_METADATA,
                DirectoryList::TMP_MATERIALIZATION_DIR,
                DirectoryList::STATIC_VIEW,
            ]
        );
        $this->setStoreMode(State::MODE_DEFAULT);
    }

    /**
     * Get current mode information
     *
     * @return string
     * @throws \Exception
     */
    public function getMode()
    {
        $env = $this->reader->load();
        return $env[State::PARAM_MODE] ?? null;
    }

    /**
     * Store mode in env.php
     *
     * @param string $mode
     * @return void
     */
    protected function setStoreMode($mode)
    {
        $this->saveAppConfigs($mode);
        $data = [
            ConfigFilePool::APP_ENV => [
                State::PARAM_MODE => $mode
            ]
        ];
        $this->writer->saveConfig($data);
    }

    /**
     * Save application configs while switching mode
     *
     * @param string $mode
     * @return void
     */
    private function saveAppConfigs($mode)
    {
        $configs = $this->configProvider->getConfigs($this->getMode(), $mode);
        foreach ($configs as $path => $value) {
            $this->emulatedAreaProcessor->process(function () use ($path, $value) {
                $this->processorFacadeFactory->create()->processWithLockTarget(
                    $path,
                    $value,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    true
                );
            });
            $this->output->writeln('Config "' . $path . ' = ' . $value . '" has been saved.');
        }
    }
}
