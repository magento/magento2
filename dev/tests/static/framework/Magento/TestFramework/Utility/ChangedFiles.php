<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $fileHelper = \Magento\TestFramework\Utility\Files::init();
        $allPhpFiles = $fileHelper->getPhpFiles();
        if (isset($_ENV['INCREMENTAL_BUILD'])) {
            $phpFiles = file($changedFilesList, FILE_IGNORE_NEW_LINES);
            foreach ($phpFiles as $key => $phpFile) {
                $phpFiles[$key] = $fileHelper->getPathToSource() . '/' . $phpFile;
            }
            $phpFiles = \Magento\TestFramework\Utility\Files::composeDataSets($phpFiles);
            $phpFiles = array_intersect_key($phpFiles, $allPhpFiles);
        } else {
            $phpFiles = $allPhpFiles;
        }

        return $phpFiles;
    }
}
