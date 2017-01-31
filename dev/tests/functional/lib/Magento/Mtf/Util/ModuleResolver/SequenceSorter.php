<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\ModuleResolver;

/**
 * Module sequence sorter.
 */
class SequenceSorter implements SequenceSorterInterface
{
    /**
     * Magento ObjectManager.
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $magentoObjectManager;

    /**
     * @constructor
     */
    public function __construct()
    {
        $this->initObjectManager();
    }

    /**
     * Initialize Magento ObjectManager.
     *
     * @return void
     */
    protected function initObjectManager()
    {
        if (!$this->magentoObjectManager) {
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(
                BP,
                $_SERVER
            );
            $this->magentoObjectManager = $objectManagerFactory->create($_SERVER);
        }
    }

    /**
     * Get Magento module sequence load.
     *
     * @return array
     */
    protected function getModuleSequence()
    {
        return $this->magentoObjectManager->create('\Magento\Framework\Module\ModuleList\Loader')->load();
    }

    /**
     * Sort files according to specified sequence.
     *
     * @param array $paths
     * @return array
     */
    public function sort(array $paths)
    {
        $sortedPaths = [];
        $modules = array_keys($this->getModuleSequence());
        foreach ($modules as $module) {
            foreach ($paths as $key => $path) {
                $modulePath = realpath(MTF_TESTS_PATH . str_replace('_', DIRECTORY_SEPARATOR, $module));
                $path = realpath($path);
                if (strpos($path, $modulePath) !== false) {
                    $sortedPaths[] = $path;
                    unset($paths[$key]);
                }
            }
        }
        $sortedPaths = array_merge($sortedPaths, $paths);

        return $sortedPaths;
    }
}
