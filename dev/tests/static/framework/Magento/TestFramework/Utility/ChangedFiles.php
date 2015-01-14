<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Utility;

/**
 * A helper to gather various changed files
 * if INCREMENTAL_BUILD env variable is set by CI build infrastructure, only files changed in the
 * branch are gathered, otherwise all files
 */
class ChangedFiles
{
    /**
     * Returns array of PHP-files, that use or declare Magento application classes and Magento libs
     *
     * @param string $changedFilesList
     * @return array
     */
    public static function getPhpFiles($changedFilesList)
    {
        $fileHelper = \Magento\Framework\Test\Utility\Files::init();
        $allPhpFiles = $fileHelper->getPhpFiles();
        if (isset($_ENV['INCREMENTAL_BUILD'])) {
            $phpFiles = file($changedFilesList, FILE_IGNORE_NEW_LINES);
            foreach ($phpFiles as $key => $phpFile) {
                $phpFiles[$key] = $fileHelper->getPathToSource() . '/' . $phpFile;
            }
            $phpFiles = \Magento\Framework\Test\Utility\Files::composeDataSets($phpFiles);
            $phpFiles = array_intersect_key($phpFiles, $allPhpFiles);
        } else {
            $phpFiles = $allPhpFiles;
        }

        return $phpFiles;
    }
}
