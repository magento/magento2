<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\RequireUpdateDryRunCommand;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Filesystem\Driver\File;

/**
 * This class checks for dependencies between components after an upgrade. It is used in readiness check.
 */
class DependencyReadinessCheck
{
    /**
     * @var ComposerJsonFinder
     */
    private $composerJsonFinder;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var RequireUpdateDryRunCommand
     */
    private $requireUpdateDryRunCommand;

    /**
     * @var File
     */
    private $file;

    /**
     * @var MagentoComposerApplication
     */
    private $magentoComposerApplication;

    /**
     * Constructor
     *
     * @param ComposerJsonFinder $composerJsonFinder
     * @param DirectoryList $directoryList
     * @param File $file
     * @param MagentoComposerApplicationFactory $composerAppFactory
     */
    public function __construct(
        ComposerJsonFinder $composerJsonFinder,
        DirectoryList $directoryList,
        File $file,
        MagentoComposerApplicationFactory $composerAppFactory
    ) {
        $this->composerJsonFinder = $composerJsonFinder;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->requireUpdateDryRunCommand = $composerAppFactory->createRequireUpdateDryRunCommand();
        $this->magentoComposerApplication = $composerAppFactory->create();
    }

    /**
     * Run Composer dependency check
     *
     * @param array $packages
     * @return array
     * @throws \Exception
     */
    public function runReadinessCheck(array $packages)
    {
        $composerJson = $this->composerJsonFinder->findComposerJson();
        $this->file->copy($composerJson, $this->directoryList->getPath(DirectoryList::VAR_DIR) .  '/composer.json');
        $workingDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        try {
            foreach ($packages as $package) {
                if (strpos($package, 'magento/product-enterprise-edition') !== false) {
                    $this->magentoComposerApplication->runComposerCommand(
                        [
                            'command' => 'remove',
                            'packages' => ['magento/product-community-edition'],
                            '--no-update' => true
                        ],
                        $workingDir
                    );
                }
            }
            $this->requireUpdateDryRunCommand->run($packages, $workingDir);
            return ['success' => true];
        } catch (\RuntimeException $e) {
            $message = str_replace(PHP_EOL, '<br/>', htmlspecialchars($e->getMessage()));
            return ['success' => false, 'error' => $message];
        }
    }
}
