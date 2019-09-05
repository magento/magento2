<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\FileManager;
use Magento\Translation\Model\Js\Config as JsConfig;

/**
 * View model responsible for getting translate dictionary file for the layout.
 */
class Dictionary implements ArgumentInterface
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var JsConfig
     */
    private $config;

    /**
     * @param FileManager $fileManager
     * @param JsConfig $config
     */
    public function __construct(
        FileManager $fileManager,
        JsConfig $config
    ) {
        $this->fileManager = $fileManager;
        $this->config = $config;
    }

    /**
     * Get translation dictionary file as an asset for the page.
     *
     * @return string
     */
    public function getTranslationDictionaryFile(): string
    {
        $translateDictionaryConfig = $this->fileManager->createTranslateDictionaryConfigAsset(JsConfig::DICTIONARY_FILE_NAME);

        return $translateDictionaryConfig->getUrl();
    }

    /**
     * Is js translation set to dictionary mode
     *
     * @return bool
     */
    public function dictionaryEnabled(): bool
    {
        return $this->config->dictionaryEnabled();
    }
}
