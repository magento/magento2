<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\ModuleResolver;

/**
 * Module sequence sorter.
 */
class SequenceSorter implements SequenceSorterInterface
{
    /**
     * Get Magento module sequence load.
     *
     * @return array
     */
    protected function getModuleSequence()
    {
        $ds = DIRECTORY_SEPARATOR;
        return json_decode(file_get_contents(MTF_BP . $ds . 'generated' . $ds . 'moduleSequence.json'), true);
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
