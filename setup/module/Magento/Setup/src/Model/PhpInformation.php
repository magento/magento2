<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class PhpInformation
{
    /**
     * List of required extensions
     *
     * @var array
     */
    protected $required;

    /**
     * List of currently installed extensions
     *
     * @var array
     */
    protected $current = [];

    /**
     * Interface to read composer.lock file
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $rootDir;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Retrieves required php version
     *
     * @return string
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function getRequiredPhpVersion()
    {
        $composerInfo = $this->getComposerInfo();
        if (!empty($composerInfo['platform']['php'])) {
            return $composerInfo['platform']['php'];
        } else {
            throw new \Exception('Missing key \'platform=>php\' in \'composer.lock\' file');
        }
    }

    /**
     * Retrieve list of required extensions
     *
     * Collect required extensions from composer.lock file
     *
     * @return array
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function getRequired()
    {
        if (null === $this->required) {
            $composerInfo = $this->getComposerInfo();
            $declaredDependencies = [];

            if (!empty($composerInfo['platform-dev'])) {
                $declaredDependencies = array_merge($declaredDependencies, array_keys($composerInfo['platform-dev']));
            } else {
                throw new \Exception('Missing key \'platform-dev\' in \'composer.lock\' file');
            }
            if (!empty($composerInfo['packages'])) {
                foreach ($composerInfo['packages'] as $package) {
                    if (!empty($package['require'])) {
                        $declaredDependencies = array_merge($declaredDependencies, array_keys($package['require']));
                    }
                }
            } else {
                throw new \Exception('Missing key \'packages\' in \'composer.lock\' file');
            }
            if ($declaredDependencies) {
                $declaredDependencies = array_unique($declaredDependencies);
                $phpDependencies = [];
                foreach ($declaredDependencies as $dependency) {
                    if (stripos($dependency, 'ext-') === 0) {
                        $phpDependencies[] = substr($dependency, 4);
                    }
                }
                $this->required = array_unique($phpDependencies);
            }
        }
        return $this->required;
    }

    /**
     * Checks existence of composer.lock and returns its contents
     *
     * @return array
     * @throws \Exception
     */
    private function getComposerInfo()
    {
        if (!$this->rootDir->isExist('composer.lock')) {
            throw new \Exception('Cannot read \'composer.lock\' file');
        }
        return json_decode($this->rootDir->readFile('composer.lock'), true);
    }

    /**
     * Retrieve list of currently installed extensions
     *
     * @return array
     */
    public function getCurrent()
    {
        if (!$this->current) {
            $this->current = array_map('strtolower', get_loaded_extensions());
        }
        return $this->current;
    }
}
