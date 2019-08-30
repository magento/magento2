<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Translation\Model\Inline\File as TranslationFile;
use Magento\Framework\View\Asset\File\FallbackContext as FileFallbackContext;
use Magento\Framework\View\Asset\File;

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
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $driverFile;

    /**
     * @var TranslationFile
     */
    private $translationFile;

    /**
     * @var FileFallbackContext
     */
    private $staticContext;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param TranslationFile $translationFile
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \Magento\Translation\Model\Inline\File $translationFile = null
    ) {
        $this->assetRepo = $assetRepo;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
        $this->translationFile = $translationFile ?: ObjectManager::getInstance()->get(TranslationFile::class);
        $this->staticContext = $assetRepo->getStaticViewFileContext();
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
     * @return string
     */
    public function getTranslationFilePath()
    {
        return $this->assetRepo->getStaticViewFileContext()->getPath();
    }

    /**
     * @param string $content
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

    /**
     * Create a view asset for translate dictionary config.
     *
     * @param string $fileName
     * @return File
     */
    public function createTranslateDictionaryConfigAsset(string $fileName): File
    {
        $relPath = $this->staticContext->getConfigPath() . '/' . $fileName;
        return $this->assetRepo->createArbitrary($relPath, '');
    }
}
