<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Prepares list of magento specific files and directory paths that updater will need access to perform the upgrade
 */
class PathBuilder
{
    const MAGENTO_BASE_PACKAGE_RELATIVE_PATH = 'magento/magento2-base';

    const COMPOSER_JSON_FILE_NAME = 'composer.json';

    const COMPOSER_KEY_EXTRA = 'extra';

    const COMPOSER_KEY_MAP = 'map';

    const VENDOR_PATH_FILE = 'vendor_path.php';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * PathBuilder constructor.
     * @param DirectoryList $directoryList
     */
    public function __construct(
        DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * Builds list of important files and directory paths that used by magento that updater application will need
     * access to perform upgrade operation
     *
     * @return string []
     * @throws \Magento\Setup\Exception
     */
    public function build()
    {
        // Locate composer.json for magento2-base module and read the extra map section for the list of
        // magento specific files and directories that updater will need access to perform the upgrade

        $vendorPath = $this->directoryList->getPath(DirectoryList::CONFIG) . '/' . self::VENDOR_PATH_FILE;
        $vendorDir = require "{$vendorPath}";

        $basePackageComposerFilePath =
            $vendorDir
            . '/'
            . self::MAGENTO_BASE_PACKAGE_RELATIVE_PATH
            . '/' . self::COMPOSER_JSON_FILE_NAME;
        if (!file_exists($basePackageComposerFilePath)) {
            throw new \Magento\Setup\Exception(
                'Could not locate '
                . self::MAGENTO_BASE_PACKAGE_RELATIVE_PATH
                . ' '
                . self::COMPOSER_JSON_FILE_NAME
                . ' file.'
            );
        }
        $composerJsonFileData = json_decode(file_get_contents($basePackageComposerFilePath), true);
        $extraMappings = $composerJsonFileData[self::COMPOSER_KEY_EXTRA][self::COMPOSER_KEY_MAP];
        $fileAndPathList = [];
        foreach ($extraMappings as $map) {
            $fileAndPathList[] = $map[1];
        }
        return $fileAndPathList;
    }
}
