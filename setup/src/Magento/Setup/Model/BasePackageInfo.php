<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\FileSystem\Directory\ReadFactory;

/**
 * Information about the Magento base package.
 *
 */
class BasePackageInfo
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
     * Get the list of files and directory paths from magento-base extra/map section.
     *
     * @return string []
     * @throws \Magento\Setup\Exception
     */
    public function getPaths()
    {
        // Locate composer.json for magento2-base module
        $filesPathList = [];
        $vendorDir = require VENDOR_PATH;
        $basePackageComposerFilePath = $vendorDir . '/' . self::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE;
        if (!$this->reader->isExist($basePackageComposerFilePath)) {
            throw new \Magento\Setup\Exception(
                'Could not locate ' . self::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE . ' file.'
            );
        }
        if (!$this->reader->isReadable($basePackageComposerFilePath)) {
            throw new \Magento\Setup\Exception(
                'Could not read ' . self::MAGENTO_BASE_PACKAGE_COMPOSER_JSON_FILE . ' file.'
            );
        }

        // Fill array with list of files and directories from extra/map section
        $composerJsonFileData = json_decode($this->reader->readFile($basePackageComposerFilePath), true);
        if (!isset($composerJsonFileData[self::COMPOSER_KEY_EXTRA][self::COMPOSER_KEY_MAP])) {
            return $filesPathList;
        }
        $extraMappings = $composerJsonFileData[self::COMPOSER_KEY_EXTRA][self::COMPOSER_KEY_MAP];
        foreach ($extraMappings as $map) {
            $filesPathList[] = $map[1];
        }
        return $filesPathList;
    }
}
