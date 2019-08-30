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
     * @param Template\Context $context
     * @param PageConfig $pageConfig
     * @param FileManager $fileManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageConfig $pageConfig,
        FileManager $fileManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageConfig = $pageConfig;
        $this->fileManager = $fileManager;
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
}
