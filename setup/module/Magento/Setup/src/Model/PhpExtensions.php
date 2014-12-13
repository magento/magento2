<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class PhpExtensions
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
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $rootDir;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
    }

    /**
     * Retrieve list of required extensions
     *
     * Collect required extensions from composer.lock file
     *
     * @return array
     */
    public function getRequired()
    {
        if (null === $this->required) {
            if (!$this->rootDir->isExist('composer.lock')) {
                $this->required = [];
                return $this->required;
            }
            $composerInfo = json_decode($this->rootDir->readFile('composer.lock'), true);
            $declaredDependencies = [];

            if (!empty($composerInfo['platform-dev'])) {
                $declaredDependencies = array_merge($declaredDependencies, array_keys($composerInfo['platform-dev']));
            }
            if (!empty($composerInfo['packages'])) {
                foreach ($composerInfo['packages'] as $package) {
                    if (!empty($package['require'])) {
                        $declaredDependencies = array_merge($declaredDependencies, array_keys($package['require']));
                    }
                }
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
