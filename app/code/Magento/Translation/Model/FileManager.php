<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Asset\Repository;

/**
 * A service for handling Translation config files
 */
class FileManager
{
    /**
     * File name of RequireJs inline translation config
     */
    const TRANSLATION_CONFIG_FILE_NAME = 'Magento_Translation/js/i18n-config.js';

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $driverFile;

    /**
     * @param Repository $assetRepo
     * @param DirectoryList $directoryList
     * @param File $driverFile
     */
    public function __construct(
        Repository $assetRepo,
        DirectoryList $directoryList,
        File $driverFile
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
     * Gets current js-translation.json timestamp
     *
     * @return string|void
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
     * Retrieve full path for translation file
     *
     * @return string
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
     * Retrieve path for translation file
     *
     * @return string
     */
    public function getTranslationFilePath()
    {
        return $this->assetRepo->getStaticViewFileContext()->getPath();
    }

    /**
     * Update translation file with content
     *
     * @param string $content
     *
     * @return void
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

    /**
     * Calculate translation file version hash.
     *
     * @return string
     */
    public function getTranslationFileVersion()
    {
        $translationFile = $this->getTranslationFileFullPath();
        $translationFileHash = '';

        if ($this->driverFile->isExists($translationFile)) {
            $translationFileHash = sha1_file($translationFile);
        }

        return sha1($translationFileHash . $this->getTranslationFilePath());
    }
}
