<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model;

use Magento\Framework\App\State;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Filesystem
 *
 * A class to manage Magento modes
 */
class Filesystem
{
    /**
     * File access permissions
     *
     * @deprecated
     */
    const PERMISSIONS_FILE = 0640;

    /**
     * Directory access permissions
     *
     * @deprecated
     */
    const PERMISSIONS_DIR = 0750;

    /**
     * Default theme when no theme is stored in configuration
     */
    const DEFAULT_THEME = 'Magento/blank';

    /** @var \Magento\Framework\App\DeploymentConfig\Writer */
    private $writer;

    /** @var \Magento\Framework\App\DeploymentConfig\Reader */
    private $reader;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Framework\Filesystem */
    private $filesystem;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    private $driverFile;

    /** @var \Magento\Store\Model\Config\StoreView */
    private $storeView;

    /** @var \Magento\Framework\Shell */
    private $shell;

    /** @var  string */
    private $functionCallPath;

    /**
     * @param \Magento\Framework\App\DeploymentConfig\Writer $writer
     * @param \Magento\Framework\App\DeploymentConfig\Reader $reader
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param \Magento\Store\Model\Config\StoreView $storeView
     * @param \Magento\Framework\Shell $shell
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig\Writer $writer,
        \Magento\Framework\App\DeploymentConfig\Reader $reader,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Store\Model\Config\StoreView $storeView,
        \Magento\Framework\Shell $shell
    ) {
        $this->writer = $writer;
        $this->reader = $reader;
        $this->objectManager = $objectManager;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->storeView = $storeView;
        $this->shell = $shell;
        $this->functionCallPath = 'php -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    /**
     * Regenerate static
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function regenerateStatic(
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        // Сlean up /var/generation, /var/di/, /var/view_preprocessed and /pub/static directories
        $this->cleanupFilesystem(
            [
                DirectoryList::CACHE,
                DirectoryList::GENERATION,
                DirectoryList::DI,
                DirectoryList::TMP_MATERIALIZATION_DIR
            ]
        );

        // Trigger static assets compilation and deployment
        $this->deployStaticContent($output);
        // Trigger code generation
        $this->compile($output);
    }

    /**
     * Deploy static content
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function deployStaticContent(
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $output->writeln('Static content deployment start');
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function compile(
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $output->writeln('Start compilation');
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
    public function cleanupFilesystem($directoryCodeList)
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
     *
     * @deprecated
     */
    protected function changePermissions($directoryCodeList, $dirPermissions, $filePermissions)
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
     *
     * @deprecated
     */
    public function lockStaticResources()
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
