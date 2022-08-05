<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Utility;

/**
 * Helper class to search files by provided directory and file pattern.
 */
class FilesSearch
{
    /**
     * Read files from generated lists.
     *
     * @param string $listsBaseDir
     * @param string $listFilePattern
     * @param callable $noListCallback
     * @return string[]
     */
    public static function getFilesFromListFile(
        string $listsBaseDir,
        string $listFilePattern,
        callable $noListCallback
    ): array {
        $filesDefinedInList = [];
        $listFiles = glob($listsBaseDir . '/_files/' . $listFilePattern);
        if (!empty($listFiles)) {
            foreach ($listFiles as $listFile) {
                $filesDefinedInList[] = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            $filesDefinedInList = array_merge([], ...$filesDefinedInList);
        } else {
            $filesDefinedInList = call_user_func($noListCallback);
        }
        array_walk(
            $filesDefinedInList,
            function (&$file) {
                $file = BP . '/' . $file;
            }
        );
        $filesDefinedInList = array_values(array_unique($filesDefinedInList));

        return $filesDefinedInList;
    }
}
