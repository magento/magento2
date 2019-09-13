<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\Js\Config as JsConfig;
use Magento\Framework\App\State as AppState;

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
     * @param AssetRepository $assetRepo
     * @param AppState $appState
     * @param Filesystem $filesystem
     */
    public function __construct(
        AssetRepository $assetRepo,
        AppState $appState,
        Filesystem $filesystem
    ) {
        $this->assetRepo = $assetRepo;
        $this->appState = $appState;
        $this->filesystem = $filesystem;
    }

    /**
     * Get translation dictionary file content.
     *
     * @return string
     */
    public function getTranslationDictionary(): string
    {
        if ($this->appState->getMode() === AppState::MODE_PRODUCTION) {
            try {
                $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);
                $staticViewFilePath = $this->filesystem->getDirectoryRead(
                    DirectoryList::STATIC_VIEW
                )->getAbsolutePath();
                $content = file_get_contents($staticViewFilePath . $asset->getPath());
            } catch (LocalizedException $e) {
                $content = '';
            }
        } else {
            try {
                $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);
                $content = $asset->getContent();
            } catch (LocalizedException | NotFoundException $e) {
                $content = '';
            }
        }

        return $content;
    }
}
