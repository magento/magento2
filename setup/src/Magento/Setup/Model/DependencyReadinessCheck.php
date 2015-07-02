<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Composer\MagentoComposerApplication;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Filesystem;

/**
 * This class checks for dependencies between components after an upgrade
 */
class DependencyReadinessCheck
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MagentoComposerApplication
     */
    private $composerApp;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param MagentoComposerApplicationFactory $composerAppFactory
     */
    public function __construct(Filesystem $filesystem, MagentoComposerApplicationFactory $composerAppFactory)
    {
        $this->filesystem = $filesystem;
        $this->composerApp = $composerAppFactory->create();
    }

    public function runReadinessCheck()
    {
        // TODO: copy composer.json file to var
        $workingDir = '';
        $varWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $result = $this->composerApp->runComposerCommand(['command' => 'update', '--dry-run' => true], $workingDir);
        if ($this->parseOutput($result)) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    /**
     * Parse Composer output
     *
     * @param string $output
     * @return bool
     */
    private function parseOutput($output)
    {
        return true;
    }
}
