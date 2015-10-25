<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * A service for handling Translation config files
 */
class FileManager
{
    /**
     * File name of RequireJs inline translation config
     */
    const TRANSLATION_CONFIG_FILE_NAME = 'Magento_Translation/js/i18n-config.js';

    /** @var \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Driver\File */
    private $driverFile;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile,
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile
    ) {
        $this->assetRepo = $assetRepo;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
    }

    /**
     * Create a view asset representing the requirejs config.config property for inline translation
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createTranslateConfigAsset()
    {
        return $this->assetRepo->createArbitrary(
            $this->assetRepo->getStaticViewFileContext()->getPath() . '/' . self::TRANSLATION_CONFIG_FILE_NAME,
            ''
        );
    }

    /**
     * gets current js-translation.json timestamp
     *
     * @return string|null
     */
    public function getJsonTimestamp()
    {
        if ($this->driverFile->isExists($this->getTranslationFileFullPath())) {
            return filemtime($this->getTranslationFileFullPath());
        }
        return null;
    }

    /**
     * @return string
     */
    public function getTranslationFileFullPath()
    {
        return $this->directoryList->getPath(DirectoryList::STATIC_VIEW) .
        \DIRECTORY_SEPARATOR .
        $this->assetRepo->getStaticViewFileContext()->getPath() .
        \DIRECTORY_SEPARATOR .
        Js\Config::DICTIONARY_FILE_NAME;
    }

    /**
     * @return string
     */
    public function getTranslationPath()
    {
        return $this->assetRepo->getStaticViewFileContext()->getPath();

    }
}
