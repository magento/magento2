<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Utility\File\RegexIteratorFactory;

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
        $fileUtilities = new File(Files::init(), new RegexIteratorFactory());
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
                $phpFiles = Files::composeDataSets($phpFiles);
                $phpFiles = array_intersect_key($phpFiles, $fileUtilities->getPhpFiles());
            }
        } else {
            $phpFiles = $fileUtilities->getPhpFiles();
        }

        return $phpFiles;
    }
}
