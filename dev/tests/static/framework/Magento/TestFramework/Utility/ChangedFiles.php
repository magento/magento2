<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\TestFramework\Utility;

use Magento\Framework\Test\Utility\Files;

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
        if (isset($_ENV['INCREMENTAL_BUILD'])) {
            $phpFiles = Files::readLists($changedFilesList);
            if (!empty($phpFiles)) {
                $phpFiles = \Magento\Framework\Test\Utility\Files::composeDataSets($phpFiles);
                $phpFiles = array_intersect_key($phpFiles, $fileHelper->getPhpFiles());
            }
        } else {
            $phpFiles = $fileHelper->getPhpFiles();
        }

        return $phpFiles;
    }
}
