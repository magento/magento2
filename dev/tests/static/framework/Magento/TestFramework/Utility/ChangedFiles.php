<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Utility;

use Magento\Framework\App\Utility\Files;

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
        $fileHelper = \Magento\Framework\App\Utility\Files::init();
        if (isset($_ENV['INCREMENTAL_BUILD'])) {
            $phpFiles = [];
            foreach (glob($changedFilesList) as $listFile) {
                $phpFiles = array_merge($phpFiles, file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
            }
            array_walk(
                $phpFiles,
                function (&$file) {
                    $file = BP . '/' . $file;
                }
            );
            if (!empty($phpFiles)) {
                $phpFiles = \Magento\Framework\App\Utility\Files::composeDataSets($phpFiles);
                $phpFiles = array_intersect_key($phpFiles, $fileHelper->getPhpFiles(
                    Files::INCLUDE_APP_CODE
                    | Files::INCLUDE_PUB_CODE
                    | Files::INCLUDE_LIBS
                    | Files::INCLUDE_TEMPLATES
                    | Files::INCLUDE_TESTS
                    | Files::AS_DATA_SET
                    | Files::INCLUDE_NON_CLASSES
                ));
            }
        } else {
            $phpFiles = $fileHelper->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::AS_DATA_SET
                | Files::INCLUDE_NON_CLASSES
            );
        }

        return $phpFiles;
    }
}
