<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\Js\Config as JsConfig;
use Magento\Framework\App\State as AppState;

/**
 * View model responsible for handling translation dictionary in layout.
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
     * @var Escaper
     */
    private $escaper;

    /**
     * @param AssetRepository $assetRepo
     * @param AppState $appState
     * @param Escaper $escaper
     */
    public function __construct(
        AssetRepository $assetRepo,
        AppState $appState,
        Escaper $escaper
    ) {
        $this->assetRepo = $assetRepo;
        $this->appState = $appState;
        $this->escaper = $escaper;
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
        $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);

        return $asset->getContent();
    }

    /**
     * Get translation dictionary url.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getTranslationDictionaryUrl(): string
    {
        $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);

        return $asset->getUrl();
    }

    /**
     * Check if application is in production mode.
     *
     * @return bool
     */
    public function isAppStateProduction(): bool
    {
        return $this->appState->getMode() === AppState::MODE_PRODUCTION;
    }

    /**
     * Escape URL.
     *
     * @param string $url
     * @return string
     */
    public function escapeUrl(string $url): string
    {
        return $this->escaper->escapeUrl($url);
    }
}
