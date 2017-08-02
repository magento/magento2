<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * A service for handling Translation config files
 * @since 2.0.0
 */
class FileManager
{
    /**
     * File name of RequireJs inline translation config
     */
    const TRANSLATION_CONFIG_FILE_NAME = 'Magento_Translation/js/i18n-config.js';

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     * @since 2.0.0
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     * @since 2.0.0
     */
    private $driverFile;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile,
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return string|void
     * @since 2.0.0
     */
    public function getTranslationFileTimestamp()
    {
        $translationFilePath = $this->getTranslationFileFullPath();
        if ($this->driverFile->isExists($translationFilePath)) {
            $statArray = $this->driverFile->stat($translationFilePath);
            if (array_key_exists('mtime', $statArray)) {
                return $statArray['mtime'];
            }
        }
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getTranslationFileFullPath()
    {
        return $this->directoryList->getPath(DirectoryList::STATIC_VIEW) .
        \DIRECTORY_SEPARATOR .
        $this->assetRepo->getStaticViewFileContext()->getPath() .
        \DIRECTORY_SEPARATOR .
        Js\Config::DICTIONARY_FILE_NAME;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTranslationFilePath()
    {
        return $this->assetRepo->getStaticViewFileContext()->getPath();
    }

    /**
     * @param string $content
     * @return void
     * @since 2.1.0
     */
    public function updateTranslationFileContent($content)
    {
        $translationDir = $this->directoryList->getPath(DirectoryList::STATIC_VIEW) .
            \DIRECTORY_SEPARATOR .
            $this->assetRepo->getStaticViewFileContext()->getPath();
        if (!$this->driverFile->isExists($this->getTranslationFileFullPath())) {
            $this->driverFile->createDirectory($translationDir);
        }
        $this->driverFile->filePutContents($this->getTranslationFileFullPath(), $content);
    }
}
