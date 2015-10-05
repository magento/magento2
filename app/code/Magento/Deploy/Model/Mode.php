<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\Config\StoreView;
use Magento\Developer\Console\Command\CssDeployCommand;

/**
 * A class to manage Magento modes
 *
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 * @SuppressWarnings("PMD.ExcessiveParameterList")
 */
class Mode
{
    /**
     * File access permissions
     */
    const PERMISSIONS_FILE = 0640;

    /**
     * Directory access permissions
     */
    const PERMISSIONS_DIR = 0750;

    /**
     * Default theme when no theme is stored in configuration
     */
    const DEFAULT_THEME = 'Magento/blank';

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    /** @var \Magento\Framework\App\DeploymentConfig\Writer */
    private $writer;

    /** @var \Magento\Framework\App\DeploymentConfig\Reader */
    private $reader;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Filesystem */
    private $filesystem;

    /** @var Filesystem */
    private $directoryList;

    /** @var File */
    private $driverFile;

    /** @var StoreView */
    private $storeView;

    /** @var \Magento\Framework\Shell */
    private $shell;

    /** @var  string */
    private $functionCallPath;
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Writer $writer
     * @param Reader $reader
     * @param ObjectManagerInterface $objectManager
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param File $driverFile
     * @param StoreView $storeView
     * @param \Magento\Framework\Shell $shell
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        Writer $writer,
        Reader $reader,
        ObjectManagerInterface $objectManager,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        File $driverFile,
        StoreView $storeView,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\App\MaintenanceMode $maintenanceMode
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->writer = $writer;
        $this->reader = $reader;
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->storeView = $storeView;
        $this->shell = $shell;
        $this->maintenanceMode = $maintenanceMode;
        $this->functionCallPath = 'php -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    /**
     * Enable production mode
     *
     * @return void
     */
    public function enableProductionMode()
    {
        $this->enableMaintenanceMode($this->output);
        // Сlean up /var/generation, /var/di/, /var/view_preprocessed and /pub/static directories
        $this->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATION,
                DirectoryList::DI,
                DirectoryList::TMP_MATERIALIZATION_DIR,
                DirectoryList::STATIC_VIEW,
            ]
        );
        // Trigger static assets compilation and deployment
        $this->deployStaticContent($this->output);
        $this->deployCss($this->output);
        // Trigger code generation
        $this->compile($this->output);
        $this->disableMaintenanceMode($this->output);
        $this->lockStaticResources();
        $this->setStoreMode(State::MODE_PRODUCTION);
    }

    /**
     * Only lock static resource locations and set store mode, without handling static content
     *
     * @return void
     */
    public function enableProductionModeMinimal()
    {
        $this->lockStaticResources();
        $this->setStoreMode(State::MODE_PRODUCTION);
    }

    /**
     * Enable Developer mode
     *
     * @return void
     */
    public function enableDeveloperMode()
    {
        $this->cleanupFilesystem(
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

    /**
     * Deploy CSS
     *
     * @param OutputInterface $output
     * @return void
     */
    private function deployCss(OutputInterface $output)
    {
        $themeLocalePairs = $this->storeView->retrieveThemeLocalePairs();
        foreach ($themeLocalePairs as $themeLocalePair) {
            $theme = $themeLocalePair['theme'] ?: self::DEFAULT_THEME;
            $cmd = $this->functionCallPath . 'dev:css:deploy less'
                . ' --' . CssDeployCommand::THEME_OPTION . '="' . $theme . '"'
                . ' --' . CssDeployCommand::LOCALE_OPTION . '="' . $themeLocalePair['locale'] . '"';

            /**
             * @todo build a solution that does not depend on exec
             */
            $execOutput = $this->shell->execute($cmd);
            $output->writeln($execOutput);
        }
        $output->writeln('CSS deployment complete');
    }

    /**
     * Deploy static content
     *
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    private function deployStaticContent(OutputInterface $output)
    {
        $cmd = $this->functionCallPath . 'setup:static-content:deploy '
            . implode(' ', $this->storeView->retrieveLocales());

        /**
         * @todo build a solution that does not depend on exec
         */
        $execOutput = $this->shell->execute($cmd);
        $output->writeln($execOutput);
        $output->writeln('Static content deployment complete');
    }

    /**
     * Runs code multi-tenant compiler to generate code and DI information
     *
     * @param OutputInterface $output
     * @return void
     */
    private function compile(OutputInterface $output)
    {
        $this->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATION,
                DirectoryList::DI,
            ]
        );
        $cmd = $this->functionCallPath . 'setup:di:compile-multi-tenant';

        /**
         * exec command is necessary for now to isolate the autoloaders in the compiler from the memory state
         * of this process, which would prevent some classes from being generated
         *
         * @todo build a solution that does not depend on exec
         */
        $execOutput = $this->shell->execute($cmd);
        $output->writeln($execOutput);
        $output->writeln('Compilation complete');
    }

    /**
     * Deletes specified directories by code
     *
     * @param array $directoryCodeList
     * @return void
     */
    private function cleanupFilesystem($directoryCodeList)
    {
        $excludePatterns = ['#.htaccess#', '#deployed_version.txt#'];
        foreach ($directoryCodeList as $code) {
            if ($code == DirectoryList::STATIC_VIEW) {
                $directoryPath = $this->directoryList->getPath(DirectoryList::STATIC_VIEW);
                if ($this->driverFile->isExists($directoryPath)) {
                    $files = $this->driverFile->readDirectory($directoryPath);
                    foreach ($files as $file) {
                        foreach ($excludePatterns as $pattern) {
                            if (preg_match($pattern, $file)) {
                                continue 2;
                            }
                        }
                        if ($this->driverFile->isFile($file)) {
                            $this->driverFile->deleteFile($file);
                        } else {
                            $this->driverFile->deleteDirectory($file);
                        }
                    }
                }
            } else {
                $this->filesystem->getDirectoryWrite($code)
                    ->delete();
            }
        }
    }

    /**
     * Change permissions for directories by their code
     *
     * @param array $directoryCodeList
     * @param int $dirPermissions
     * @param int $filePermissions
     * @return void
     */
    private function changePermissions($directoryCodeList, $dirPermissions, $filePermissions)
    {
        foreach ($directoryCodeList as $code) {
            $directoryPath = $this->directoryList->getPath($code);
            if ($this->driverFile->isExists($directoryPath)) {
                $this->filesystem->getDirectoryWrite($code)
                    ->changePermissionsRecursively('', $dirPermissions, $filePermissions);
            } else {
                $this->driverFile->createDirectory($directoryPath, $dirPermissions);
            }
        }
    }

    /**
     * Chenge permissions on static resources
     *
     * @return void
     */
    private function lockStaticResources()
    {
        // Lock /var/generation, /var/di/ and /var/view_preprocessed directories
        $this->changePermissions(
            [
                DirectoryList::GENERATION,
                DirectoryList::DI,
                DirectoryList::TMP_MATERIALIZATION_DIR,
            ],
            self::PERMISSIONS_DIR,
            self::PERMISSIONS_FILE
        );
    }
}
