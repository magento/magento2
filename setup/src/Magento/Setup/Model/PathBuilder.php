<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\FileSystem\Directory\ReadFactory;

/**
 * Prepares list of magento specific files and directory paths that updater will need access to perform the upgrade
 */
class PathBuilder
{
    const MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE = 'magento/magento2-base/composer.json';

    const COMPOSER_KEY_EXTRA = 'extra';

    const COMPOSER_KEY_MAP = 'map';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface $reader
     */
    private $reader;
    /**
     * Constructor
     *
     * @param ReadFactory $readFactory
     */
    public function __construct(ReadFactory $readFactory)
    {
        $this->reader = $readFactory->create(BP);
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
        $vendorDir = require VENDOR_PATH;


        $basePackageComposerFilePath = $vendorDir . '/' . self::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE;
        if (!$this->reader->isExist($basePackageComposerFilePath)) {
            throw new \Magento\Setup\Exception(
                'Could not locate ' . self::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE . ' file.'
            );
        }
        $composerJsonFileData = json_decode($this->reader->readFile($basePackageComposerFilePath), true);
        $extraMappings = $composerJsonFileData[self::COMPOSER_KEY_EXTRA][self::COMPOSER_KEY_MAP];
        $fileAndPathList = [];
        foreach ($extraMappings as $map) {
            $fileAndPathList[] = $map[1];
        }
        return $fileAndPathList;
    }
}
