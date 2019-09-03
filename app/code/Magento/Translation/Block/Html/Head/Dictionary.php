<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block\Html\Head;

use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\FileManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Translation\Model\Js\Config as JsConfig;

/**
 * Block responsible for getting translate dictionary file for the layout.
 */
class Dictionary extends Template
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var JsConfig
     */
    private $config;

    /**
     * @param Template\Context $context
     * @param PageConfig $pageConfig
     * @param FileManager $fileManager
     * @param JsConfig $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageConfig $pageConfig,
        FileManager $fileManager,
        JsConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageConfig = $pageConfig;
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
        $translateDictionaryConfigRelPath = $translateDictionaryConfig->getFilePath();

        return $this->_assetRepo->getUrl($translateDictionaryConfigRelPath);
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
