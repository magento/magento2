<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\Js\Config as JsConfig;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * View model responsible for getting translate dictionary file content for the layout.
 */
class Dictionary implements ArgumentInterface
{
    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DriverInterface
     */
    private $filesystemDriver;

    /**
     * @param AssetRepository $assetRepo
     * @param AppState $appState
     * @param Filesystem $filesystem
     * @param DriverInterface $filesystemDriver
     */
    public function __construct(
        AssetRepository $assetRepo,
        AppState $appState,
        Filesystem $filesystem,
        DriverInterface $filesystemDriver
    ) {
        $this->assetRepo = $assetRepo;
        $this->appState = $appState;
        $this->filesystem = $filesystem;
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Get translation dictionary file content.
     *
     * @return string
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getTranslationDictionary(): string
    {
        if ($this->appState->getMode() === AppState::MODE_PRODUCTION) {
            $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);
            $staticViewFilePath = $this->filesystem->getDirectoryRead(
                DirectoryList::STATIC_VIEW
            )->getAbsolutePath();
            $content = $this->filesystemDriver->fileGetContents($staticViewFilePath . $asset->getPath());
        } else {
            $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);
            $content = $asset->getContent();
        }

        return $content;
    }
}
