<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\Js\Config as JsConfig;

/**
 * View model responsible for getting translate dictionary file for the layout.
 */
class Dictionary implements ArgumentInterface
{
    /**
     * @var JsConfig
     */
    private $config;

    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @param AssetRepository $assetRepo
     * @param JsConfig $config
     */
    public function __construct(
        AssetRepository $assetRepo,
        JsConfig $config
    ) {
        $this->assetRepo = $assetRepo;
        $this->config = $config;
    }

    /**
     * Get translation dictionary file as an asset for the page.
     *
     * @return string
     */
    public function getTranslationDictionaryFile(): string
    {
        return $this->assetRepo->getUrl(JsConfig::DICTIONARY_FILE_NAME);
    }

    /**
     * Is js translation set to dictionary mode.
     *
     * @return bool
     */
    public function dictionaryEnabled(): bool
    {
        return $this->config->dictionaryEnabled();
    }
}
